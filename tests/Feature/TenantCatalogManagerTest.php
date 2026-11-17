<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class TenantCatalogManagerTest extends TestCase
{
    use RefreshDatabase;

    private function tenantWithMember(): array
    {
        $event = Event::factory()->create();
        $tenant = Tenant::factory()->create(['event_id' => $event->id]);
        $user = User::factory()->create(['role' => 'tenant', 'status' => 'active', 'email_verified_at' => now()]);
        UserTenantRole::create(['user_id' => $user->id, 'tenant_id' => $tenant->id, 'role' => 'operator']);

        return [$tenant, $user];
    }

    public function test_menu_manager_page_renders_catalog_component(): void
    {
        [$tenant, $user] = $this->tenantWithMember();

        $this->actingAs($user)
            ->get(route('tenant.catalog-manager', ['tenant' => $tenant->slug]))
            ->assertOk()
            ->assertSee('Kelola Katalog')
            ->assertSeeLivewire('catalog::catalog-manager');
    }

    public function test_member_creates_catalog_with_tenant_id_from_context(): void
    {
        [$tenant, $user] = $this->tenantWithMember();
        $category = CatalogCategory::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::actingAs($user)
            ->test('catalog::catalog-manager', ['tenantId' => $tenant->id])
            ->set('name', 'Tiket VIP Day 1')
            ->set('categoryId', $category->id)
            ->set('basePrice', 150000)
            ->set('stockQty', 50)
            ->call('createCatalog')
            ->assertHasNoErrors();

        $catalog = Catalog::withoutGlobalScope('tenant')->where('name', 'Tiket VIP Day 1')->firstOrFail();
        $this->assertSame($tenant->id, $catalog->tenant_id);
    }

    public function test_toggle_and_restock_are_atomic_and_audited(): void
    {
        [$tenant, $user] = $this->tenantWithMember();
        $category = CatalogCategory::factory()->create(['tenant_id' => $tenant->id]);
        $catalog = Catalog::factory()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'stock_qty' => 5,
            'is_available' => true,
        ]);

        Livewire::actingAs($user)
            ->test('catalog::catalog-manager', ['tenantId' => $tenant->id])
            ->call('toggle', $catalog->id)
            ->call('restock', $catalog->id, 10);

        $catalog->refresh();
        $this->assertFalse($catalog->is_available);
        $this->assertSame(15, $catalog->stock_qty);

        $this->assertSame(1, DB::table('catalog_stock_movements')->where('catalog_id', $catalog->id)->count());
        $this->assertTrue(DB::table('audit_logs')->where('entity', 'catalog')->where('action', 'toggle_availability')->exists());
    }

    public function test_non_member_cannot_access_other_tenant_catalog(): void
    {
        [$tenantA, $userA] = $this->tenantWithMember();
        $tenantB = Tenant::factory()->create(['event_id' => Event::factory()->create()->id]);

        Livewire::actingAs($userA)
            ->test('catalog::catalog-manager', ['tenantId' => $tenantB->id])
            ->assertForbidden();
    }
}