<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Catalog;
use App\Models\CommissionScheme;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\User;
use App\Models\UserPlatformRole;
use App\Models\UserTenantRole;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * UC-21 Kelola Tenant & Skema Komisi: onboarding (identitas, penanggung jawab, rekening,
 * kredensial awal), daftar dengan komisi aktif, nonaktif/aktifkan, dan riwayat komisi.
 */
class TenantOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function managerFor(Event $event, string $name = 'Bagas P.'): User
    {
        $user = User::factory()->create(['name' => $name, 'role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        UserPlatformRole::create(['user_id' => $user->id, 'event_id' => $event->id, 'role' => 'manager']);

        return $user;
    }

    /** @return array<string, string> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Warung Bu Rina', 'code' => 'RINA', 'slug' => 'warung-bu-rina', 'commission_rate' => '10',
            'pic_name' => 'Rina S.', 'pic_email' => 'rina@kantin.test',
            'bank_code' => 'BCA', 'account_holder' => 'Rina Sari', 'account_number' => '1234566721',
        ], $overrides);
    }

    public function test_onboarding_creates_owner_account_bank_account_and_sends_set_password_link(): void
    {
        Notification::fake();
        $event = Event::factory()->create();
        $this->actingAs($this->managerFor($event));

        $this->post(route('admin.tenants.store'), $this->payload())->assertRedirect()->assertSessionHas('status');

        $tenant = Tenant::query()->where('code', 'RINA')->firstOrFail();
        $owner = User::query()->where('email', 'rina@kantin.test')->firstOrFail();
        $this->assertSame('tenant', $owner->role);
        $this->assertTrue($owner->isActive());
        $this->assertTrue(UserTenantRole::query()->where(['tenant_id' => $tenant->id, 'user_id' => $owner->id, 'role' => 'owner'])->exists());

        $account = TenantBankAccount::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('6721', $account->account_last4);
        $this->assertTrue($account->is_primary);
        $this->assertStringNotContainsString('1234566721', (string) $account->getRawOriginal('account_number_cipher'));

        // Kredensial awal = tautan atur kata sandi, bukan kata sandi mentah.
        Notification::assertSentTo($owner, ResetPassword::class);
    }

    public function test_onboarding_rejects_existing_pic_email_and_missing_bank(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($this->managerFor($event));
        User::factory()->create(['email' => 'rina@kantin.test']);

        $this->post(route('admin.tenants.store'), $this->payload(['account_number' => '']))
            ->assertSessionHasErrors(['pic_email', 'account_number']);
        $this->assertFalse(Tenant::query()->where('code', 'RINA')->exists());
    }

    public function test_index_lists_pic_primary_bank_and_active_commission(): void
    {
        Notification::fake();
        $event = Event::factory()->create();
        $this->actingAs($this->managerFor($event));
        $this->post(route('admin.tenants.store'), $this->payload());

        $this->get(route('admin.tenants.index'))
            ->assertOk()
            ->assertSeeInOrder(['Warung Bu Rina', 'Rina S.', 'BCA ••6721', '10%', 'AKTIF', 'Ubah komisi', 'Nonaktifkan']);
    }

    public function test_deactivated_tenant_is_hidden_from_catalog_but_history_is_kept(): void
    {
        $event = Event::factory()->create();
        $tenant = Tenant::factory()->create(['event_id' => $event->id]);
        $catalog = Catalog::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($this->managerFor($event));

        $this->post(route('admin.tenants.status', $tenant), ['status' => 'inactive'])->assertRedirect(route('admin.tenants.index'));

        $this->assertSame('inactive', $tenant->fresh()->status);
        $this->assertFalse(app(PublicCatalogQuery::class)->forEvent($event)->contains('id', $catalog->id));
        $this->assertTrue(Catalog::query()->withoutGlobalScope('tenant')->whereKey($catalog->id)->exists(), 'data historis tidak dihapus');
        $this->assertTrue(AuditLog::query()->where(['entity' => 'tenant', 'entity_id' => (string) $tenant->id, 'action' => 'deactivated'])->exists());
        $this->get(route('admin.tenants.index'))->assertSee('NONAKTIF')->assertSee('Aktifkan');

        $this->post(route('admin.tenants.status', $tenant), ['status' => 'active']);
        $this->assertTrue(app(PublicCatalogQuery::class)->forEvent($event)->contains('id', $catalog->id));
    }

    public function test_manager_of_other_event_cannot_change_status_and_status_is_whitelisted(): void
    {
        $tenant = Tenant::factory()->create(['event_id' => Event::factory()->create()->id]);
        $outsider = $this->managerFor(Event::factory()->create(), 'Orang Lain');

        $this->actingAs($outsider)->post(route('admin.tenants.status', $tenant), ['status' => 'inactive'])->assertForbidden();
        $this->assertSame('active', $tenant->fresh()->status);

        $this->actingAs($this->managerFor($tenant->event))
            ->post(route('admin.tenants.status', $tenant), ['status' => 'deleted'])
            ->assertSessionHasErrors('status');
    }

    public function test_commission_history_shows_the_acting_manager_and_rejects_retroactive_dates(): void
    {
        Notification::fake();
        $event = Event::factory()->create();
        $this->actingAs($this->managerFor($event, 'Bagas P.'));
        $this->post(route('admin.tenants.store'), $this->payload());
        $tenant = Tenant::query()->where('code', 'RINA')->firstOrFail();

        $this->post(route('admin.tenants.commission.store', $tenant), ['commission_rate' => '12', 'effective_at' => now()->subDay()->format('Y-m-d\TH:i')])
            ->assertSessionHasErrors('effective_at');
        $this->post(route('admin.tenants.commission.store', $tenant), ['commission_rate' => '12', 'effective_at' => now()->addMonth()->format('Y-m-d\TH:i')])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, CommissionScheme::query()->withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count());
        $this->get(route('admin.tenants.edit', $tenant))
            ->assertOk()
            ->assertSee('Komisi aktif saat ini')
            ->assertSeeInOrder(['12% → berlaku', 'oleh Bagas P.', '10% → berlaku', 'oleh Bagas P.']);
    }
}
