<?php

namespace Database\Factories;

use App\Models\CatalogCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CatalogCategory> */
class CatalogCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->word(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}