<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\CommissionScheme;
use App\Models\Event;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Constraint diuji pada database (bukan melewati validasi aplikasi) — komposit FK lintas-tenant,
 * idempotency, dan invariant order induk tanpa tenant_id.
 */
class DatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_cannot_reference_another_tenants_category(): void
    {
        $event = Event::factory()->create();
        $a = Tenant::factory()->create(['event_id' => $event->id]);
        $b = Tenant::factory()->create(['event_id' => $event->id]);
        $categoryB = CatalogCategory::factory()->create(['tenant_id' => $b->id]);

        $this->expectException(QueryException::class);
        Catalog::factory()->create([
            'tenant_id' => $a->id,             // EO A
            'category_id' => $categoryB->id,   // Kategori EO B -> composite FK gagal
        ]);
    }

    public function test_modifier_option_cannot_reference_another_tenants_group(): void
    {
        $event = Event::factory()->create();
        $a = Tenant::factory()->create(['event_id' => $event->id]);
        $b = Tenant::factory()->create(['event_id' => $event->id]);
        $groupB = ModifierGroup::factory()->create(['tenant_id' => $b->id]);

        $this->expectException(QueryException::class);
        ModifierOption::factory()->create([
            'tenant_id' => $a->id,
            'group_id' => $groupB->id,
        ]);
    }

    public function test_duplicate_tenant_code_in_same_event_is_rejected(): void
    {
        $event = Event::factory()->create();
        Tenant::factory()->create(['event_id' => $event->id, 'code' => 'DUP']);

        $this->expectException(QueryException::class);
        Tenant::factory()->create(['event_id' => $event->id, 'code' => 'DUP']);
    }

    public function test_stock_movement_idempotency_key_is_unique(): void
    {
        $event = Event::factory()->create();
        $tenant = Tenant::factory()->create(['event_id' => $event->id]);
        $catalog = Catalog::factory()->create(['tenant_id' => $tenant->id]);

        $row = fn () => [
            'tenant_id' => $tenant->id,
            'catalog_id' => $catalog->id,
            'idempotency_key' => 'stk-key-1',
            'type' => 'sale',
            'quantity_delta' => -1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('catalog_stock_movements')->insert($row());

        $this->expectException(QueryException::class);
        DB::table('catalog_stock_movements')->insert($row());
    }

    public function test_orders_has_no_tenant_id_and_supports_multiple_tenant_orders(): void
    {
        $this->assertFalse(Schema::hasColumn('orders', 'tenant_id'), 'orders TIDAK boleh punya tenant_id');

        $event = Event::factory()->create();
        $a = Tenant::factory()->create(['event_id' => $event->id]);
        $b = Tenant::factory()->create(['event_id' => $event->id]);
        $ca = CommissionScheme::factory()->create(['tenant_id' => $a->id]);
        $cb = CommissionScheme::factory()->create(['tenant_id' => $b->id]);

        $orderId = DB::table('orders')->insertGetId([
            'public_id' => (string) Str::uuid(),
            'order_number' => 'ORD-1',
            'event_id' => $event->id,
            'checkout_key' => 'ck-1',
            'tracking_token_hash' => hash('sha256', 'token-1', true),
            'status' => 'pending',
            'grand_total_amount' => 500000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([[$a, $ca], [$b, $cb]] as [$t, $c]) {
            DB::table('tenant_orders')->insert([
                'order_id' => $orderId,
                'tenant_id' => $t->id,
                'commission_id' => $c->id,
                'status' => 'pending',
                'commission_rate_snapshot' => 0.1500,
                'subtotal_amount' => 250000,
                'commission_amount' => 37500,
                'net_amount' => 212500,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->assertCount(2, DB::table('tenant_orders')->where('order_id', $orderId)->get());
    }
}
