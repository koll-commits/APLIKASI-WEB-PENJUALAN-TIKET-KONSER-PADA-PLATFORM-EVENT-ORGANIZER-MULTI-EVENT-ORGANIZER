<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** CRUD event/konser EO: izin per role, isolasi antar EO, dan aturan hapus. */
class TenantConcertTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::factory()->create(['event_id' => Event::factory()->create()->id, 'status' => 'active']);
    }

    private function memberOf(Tenant $tenant, string $role = 'operator'): User
    {
        $user = User::factory()->create(['role' => 'tenant', 'status' => 'active', 'email_verified_at' => now()]);
        UserTenantRole::create(['user_id' => $user->id, 'tenant_id' => $tenant->id, 'role' => $role]);

        return $user;
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Rock in Jakarta',
            'venue' => 'Istora Senayan',
            'city' => 'Jakarta',
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addMonth()->addHours(4)->format('Y-m-d\TH:i'),
            'description' => 'Festival rock tahunan.',
            'status' => 'draft',
        ], $override);
    }

    public function test_operator_can_list_and_create_concert_for_own_tenant(): void
    {
        $tenant = $this->tenant();
        $this->actingAs($this->memberOf($tenant));

        $this->get(route('tenant.concerts.index', ['tenant' => $tenant->slug]))->assertOk();

        $this->post(route('tenant.concerts.store', ['tenant' => $tenant->slug]), $this->payload())
            ->assertRedirect(route('tenant.concerts.index', ['tenant' => $tenant->slug]));

        $this->assertDatabaseHas('events', ['tenant_id' => $tenant->id, 'name' => 'Rock in Jakarta', 'slug' => 'rock-in-jakarta']);
    }

    public function test_slug_is_unique_within_the_tenant(): void
    {
        $tenant = $this->tenant();
        $this->actingAs($this->memberOf($tenant));
        $url = route('tenant.concerts.store', ['tenant' => $tenant->slug]);

        $this->post($url, $this->payload());
        $this->post($url, $this->payload());

        $this->assertDatabaseHas('events', ['tenant_id' => $tenant->id, 'slug' => 'rock-in-jakarta-2']);
    }

    public function test_cashier_can_view_but_not_create(): void
    {
        $tenant = $this->tenant();
        $this->actingAs($this->memberOf($tenant, 'cashier'));

        $this->get(route('tenant.concerts.index', ['tenant' => $tenant->slug]))->assertOk();
        $this->post(route('tenant.concerts.store', ['tenant' => $tenant->slug]), $this->payload())->assertForbidden();
        $this->assertSame(0, Event::where('tenant_id', $tenant->id)->count());
    }

    public function test_non_member_is_forbidden(): void
    {
        $tenant = $this->tenant();
        $this->actingAs($this->memberOf($this->tenant()));

        $this->get(route('tenant.concerts.index', ['tenant' => $tenant->slug]))->assertForbidden();
    }

    public function test_cannot_edit_another_tenants_concert(): void
    {
        $a = $this->tenant();
        $b = $this->tenant();
        $concertB = Concert::factory()->create(['tenant_id' => $b->id]);
        $this->actingAs($this->memberOf($a));

        $this->get(route('tenant.concerts.edit', ['tenant' => $a->slug, 'concert' => $concertB->id]))->assertNotFound();
        $this->patch(route('tenant.concerts.update', ['tenant' => $a->slug, 'concert' => $concertB->id]), $this->payload())->assertNotFound();
    }

    public function test_operator_can_update_concert(): void
    {
        $tenant = $this->tenant();
        $concert = Concert::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($this->memberOf($tenant));

        $this->patch(route('tenant.concerts.update', ['tenant' => $tenant->slug, 'concert' => $concert->id]), $this->payload(['name' => 'Nama Baru', 'status' => 'published']))
            ->assertRedirect();

        $this->assertDatabaseHas('events', ['id' => $concert->id, 'name' => 'Nama Baru', 'status' => 'published']);
    }

    public function test_only_draft_concert_can_be_deleted(): void
    {
        $tenant = $this->tenant();
        $draft = Concert::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
        $published = Concert::factory()->published()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($this->memberOf($tenant));

        $this->delete(route('tenant.concerts.destroy', ['tenant' => $tenant->slug, 'concert' => $published->id]))->assertSessionHasErrors('concert');
        $this->assertDatabaseHas('events', ['id' => $published->id]);

        $this->delete(route('tenant.concerts.destroy', ['tenant' => $tenant->slug, 'concert' => $draft->id]))->assertRedirect();
        $this->assertDatabaseMissing('events', ['id' => $draft->id]);
    }

    public function test_validation_rejects_past_start_and_end_before_start(): void
    {
        $tenant = $this->tenant();
        $this->actingAs($this->memberOf($tenant));

        $this->post(route('tenant.concerts.store', ['tenant' => $tenant->slug]), $this->payload([
            'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('starts_at');

        $this->post(route('tenant.concerts.store', ['tenant' => $tenant->slug]), $this->payload([
            'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('ends_at');
    }
}
