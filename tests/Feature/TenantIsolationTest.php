<?php

namespace Tests\Feature;

use App\Jobs\CountTenantCatalogs;
use App\Models\Event;
use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use App\Policies\CatalogPolicy;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Matriks isolasi tenant (allow/deny) A vs B: global scope, scoped binding, policy,
 * public bypass terkontrol, dan context pada job. Dijalankan di MariaDB nyata.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Tenant, 1: Collection<int, Catalog>} */
    private function tenantWithCatalogs(int $count, ?Event $event = null, string $status = 'active'): array
    {
        $event ??= Event::factory()->create();
        $tenant = Tenant::factory()->create(['event_id' => $event->id, 'status' => $status]);
        $category = CatalogCategory::factory()->create(['tenant_id' => $tenant->id]);
        $catalogs = Catalog::factory()->count($count)->create(['tenant_id' => $tenant->id, 'category_id' => $category->id]);

        return [$tenant, $catalogs];
    }

    private function memberOf(Tenant $tenant): User
    {
        $user = User::factory()->create(['role' => 'tenant', 'status' => 'active', 'email_verified_at' => now()]);
        UserTenantRole::create(['user_id' => $user->id, 'tenant_id' => $tenant->id, 'role' => 'operator']);

        return $user;
    }

    public function test_global_scope_returns_only_active_tenant_rows(): void
    {
        [$a] = $this->tenantWithCatalogs(2);
        $this->tenantWithCatalogs(3); // tenant B

        app(TenantContext::class)->set($a);
        $this->assertSame(2, Catalog::query()->count());
        $this->assertTrue(Catalog::query()->get()->every(fn (Catalog $c) => $c->tenant_id === $a->id));
    }

    public function test_without_context_scope_is_not_applied(): void
    {
        $this->tenantWithCatalogs(2);
        $this->tenantWithCatalogs(3);

        // Tanpa context, scope tidak memfilter (alur internal SELALU mengisi context).
        $this->assertSame(5, Catalog::query()->count());
    }

    public function test_context_is_reset_between_requests(): void
    {
        [$a] = $this->tenantWithCatalogs(1);
        app(TenantContext::class)->set($a);
        $this->assertTrue(app(TenantContext::class)->has());

        // Simulasi lifecycle request/job berikutnya.
        $this->app->forgetScopedInstances();
        $this->assertFalse(app(TenantContext::class)->has(), 'TenantContext bocor antar request/job');
    }

    public function test_tenant_id_is_autofilled_on_create_within_context(): void
    {
        [$a] = $this->tenantWithCatalogs(0);
        app(TenantContext::class)->set($a);

        $category = CatalogCategory::create(['name' => 'Auto Fill']);
        $this->assertSame($a->id, $category->tenant_id);
    }

    public function test_catalog_index_returns_only_own_tenant(): void
    {
        [$a, $catalogsA] = $this->tenantWithCatalogs(2);
        $this->tenantWithCatalogs(3); // B

        $this->actingAs($this->memberOf($a));
        $response = $this->getJson(route('tenant.catalogs.index', ['tenant' => $a->slug]));

        $response->assertOk();
        $this->assertCount(2, $response->json('catalogs'));
    }

    public function test_scoped_binding_blocks_cross_tenant_menu(): void
    {
        [$a] = $this->tenantWithCatalogs(1);
        [, $catalogsB] = $this->tenantWithCatalogs(1);
        $catalogB = $catalogsB->first();

        $this->actingAs($this->memberOf($a));
        // Catalog tenant B diakses lewat route tenant A -> tidak ditemukan (scoped binding + scope).
        $this->getJson(route('tenant.catalogs.show', ['tenant' => $a->slug, 'catalog' => $catalogB->id]))
            ->assertNotFound();
    }

    public function test_member_can_toggle_own_menu(): void
    {
        [$a, $catalogsA] = $this->tenantWithCatalogs(1);
        $catalogA = $catalogsA->first();

        $this->actingAs($this->memberOf($a));
        $this->patchJson(route('tenant.catalogs.update', ['tenant' => $a->slug, 'catalog' => $catalogA->id]), [
            'is_available' => false,
        ])->assertOk()->assertJson(['ok' => true, 'is_available' => false]);

        $this->assertFalse($catalogA->refresh()->is_available);
    }

    public function test_policy_denies_cross_tenant_update(): void
    {
        [$a] = $this->tenantWithCatalogs(0);
        [, $catalogsB] = $this->tenantWithCatalogs(1);
        $catalogB = $catalogsB->first();

        app(TenantContext::class)->set($a); // aktif = A
        $this->assertFalse((new CatalogPolicy)->update(new User, $catalogB), 'Policy harus menolak catalog tenant lain');
    }

    public function test_public_catalog_query_no_cross_event_or_inactive_leak(): void
    {
        $event1 = Event::factory()->create();
        $this->tenantWithCatalogs(2, $event1);                       // aktif, available
        $this->tenantWithCatalogs(1, $event1, status: 'suspended');  // suspended -> tidak tampil
        $event2 = Event::factory()->create();
        $this->tenantWithCatalogs(4, $event2);                       // event lain -> tidak tampil

        $catalogs = app(PublicCatalogQuery::class)->forevent($event1);

        $this->assertCount(2, $catalogs, 'Hanya catalog tenant aktif dari event ini');
    }

    public function test_job_sets_and_clears_context_per_tenant(): void
    {
        [$a] = $this->tenantWithCatalogs(2);
        [$b] = $this->tenantWithCatalogs(3);

        $jobA = new CountTenantCatalogs($a->id);
        $jobA->handle(app(TenantContext::class));
        $this->assertSame(2, $jobA->result);
        $this->assertFalse(app(TenantContext::class)->has(), 'Context harus dibersihkan setelah job');

        $jobB = new CountTenantCatalogs($b->id);
        $jobB->handle(app(TenantContext::class));
        $this->assertSame(3, $jobB->result); // tidak terkontaminasi tenant A
    }
}
