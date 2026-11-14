<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\CommissionScheme;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\User;
use App\Models\UserPlatformRole;
use App\Models\UserTenantRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo deterministik & idempoten: 1 Event, 2 EO (Tenant), komisi, tiket, kursi, role.
 * Idempoten via firstOrNew + forceFill (kolom guarded seperti event_id/tenant_id diisi eksplisit).
 */
class DemoEventSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Event Demo
        $event = tap(Event::firstOrNew(['code' => 'EVT-JKT26']))
            ->forceFill([
                'slug' => 'jakarta-sound-fest-2026',
                'name' => 'Jakarta Sound Fest 2026',
                'tax_rate' => 0.1000,
                'service_fee_rate' => 0.0200,
                'status' => 'active',
            ]);
        $event->save();

        // 2. Kursi & Spot Venue Demo
        $seats = [
            ['VIP-01', 'Kursi VIP 01', 'VIP Lounge'],
            ['VIP-02', 'Kursi VIP 02', 'VIP Lounge'],
            ['FEST-01', 'Spot Festival 01', 'Festival Area'],
        ];

        foreach ($seats as [$code, $label, $zone]) {
            tap(EventSeat::firstOrNew(['event_id' => $event->id, 'code' => $code]))
                ->forceFill([
                    'event_id' => $event->id,
                    'label' => $label,
                    'zone' => $zone,
                    'status' => 'active',
                ])->save();
        }

        // 3. Blueprint 2 EO (Tenant Tiket & Merchandise)
        $blueprint = [
            'SOUND' => [
                'display' => 'Soundwave Promotor',
                'category' => 'Tiket Konser',
                'catalogs' => [
                    ['VIP Pass All Days', 750000],
                    ['Festival Day 1', 350000],
                ],
                'modifier' => [
                    'Akses Tambahan',
                    [
                        ['Backstage Access', 200000],
                        ['Fast Track Entry', 50000],
                    ],
                ],
            ],
            'MERCH' => [
                'display' => 'Official Fest Merch',
                'category' => 'Apparel & Accessories',
                'catalogs' => [
                    ['Official Tour T-Shirt', 180000],
                    ['Fest Cap & Lanyard Bundle', 85000],
                ],
                'modifier' => [
                    'Ukuran Kaos',
                    [
                        ['Size M', 0],
                        ['Size XL', 10000],
                    ],
                ],
            ],
        ];

        $tenants = [];
        foreach ($blueprint as $code => $spec) {
            $tenant = tap(Tenant::withTrashed()->firstOrNew(['event_id' => $event->id, 'code' => $code]))
                ->forceFill([
                    'event_id' => $event->id,
                    'slug' => strtolower($code).'-jkt',
                    'display_name' => $spec['display'],
                    'status' => 'active',
                    'deleted_at' => null,
                ]);
            $tenant->save();
            $tenants[$code] = $tenant;

            // Saldo Awal EO
            tap(TenantBalance::firstOrNew(['tenant_id' => $tenant->id]))
                ->forceFill([
                    'tenant_id' => $tenant->id,
                    'available_amount' => 0,
                    'held_amount' => 0,
                ])->save();

            // Skema Komisi Bagi Hasil Platform
            tap(CommissionScheme::firstOrNew(['tenant_id' => $tenant->id, 'valid_from' => now()->startOfYear()]))
                ->forceFill([
                    'tenant_id' => $tenant->id,
                    'commission_rate' => 0.1500,
                    'valid_to' => null,
                ])->save();

            // Kategori Katalog
            $category = tap(CatalogCategory::firstOrNew(['tenant_id' => $tenant->id, 'name' => $spec['category']]))
                ->forceFill([
                    'tenant_id' => $tenant->id,
                    'sort_order' => 1,
                    'is_active' => true,
                ]);
            $category->save();

            // Item Tiket / Merchandise
            foreach ($spec['catalogs'] as $i => [$name, $price]) {
                tap(Catalog::firstOrNew(['tenant_id' => $tenant->id, 'name' => $name]))
                    ->forceFill([
                        'tenant_id' => $tenant->id,
                        'category_id' => $category->id,
                        'base_price' => $price,
                        'stock_qty' => 100,
                        'is_available' => true,
                        'prep_minutes' => 0,
                    ])->save();
            }

            // Grup Modifier / Varian
            [$groupName, $options] = $spec['modifier'];
            $group = tap(ModifierGroup::firstOrNew(['tenant_id' => $tenant->id, 'name' => $groupName]))
                ->forceFill([
                    'tenant_id' => $tenant->id,
                    'min_select' => 0,
                    'max_select' => 1,
                    'is_active' => true,
                ]);
            $group->save();

            foreach ($options as [$optName, $delta]) {
                tap(ModifierOption::firstOrNew(['tenant_id' => $tenant->id, 'group_id' => $group->id, 'name' => $optName]))
                    ->forceFill([
                        'tenant_id' => $tenant->id,
                        'group_id' => $group->id,
                        'price_delta' => $delta,
                        'stock_qty' => 100,
                        'is_available' => true,
                    ])->save();
            }
        }

        // 4. Hubungkan User Demo EO / Tenant ke Tenant 'SOUND'
        foreach (['eo@stagetix.test', 'tenant@stagetix.test'] as $email) {
            $operatorUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Tenant Operator',
                    'role' => 'tenant',
                    'status' => 'active',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            UserTenantRole::firstOrCreate([
                'user_id' => $operatorUser->id,
                'tenant_id' => $tenants['SOUND']->id,
                'role' => 'operator',
            ]);
        }

        $admin = User::where('email', 'admin@stagetix.test')->first();
        if ($admin !== null) {
            UserPlatformRole::firstOrCreate(
                ['user_id' => $admin->id, 'role' => 'admin'],
                ['event_id' => $event->id],
            );
        }
    }
}
