<?php

namespace Database\Factories;

use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Catalog> */
class CatalogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            // Composite FK: Kategori dibuat untuk EO yang sama
            'category_id' => fn (array $attributes) => CatalogCategory::factory()
                ->create(['tenant_id' => $attributes['tenant_id']])->id,
            'name' => fake()->unique()->word().' Pass',
            'base_price' => fake()->numberBetween(50, 500) * 1000,
            'stock_qty' => 100,
            'is_available' => true,
            'prep_minutes' => 0,
        ];
    }
}
