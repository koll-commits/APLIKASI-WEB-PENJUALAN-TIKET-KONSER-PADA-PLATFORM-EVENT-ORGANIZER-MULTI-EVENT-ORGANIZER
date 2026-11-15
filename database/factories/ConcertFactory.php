<?php

namespace Database\Factories;

use App\Models\Concert;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Concert> */
class ConcertFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Live';

        return [
            'tenant_id' => Tenant::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'venue' => 'Gelora '.fake()->word(),
            'city' => 'Jakarta',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonth()->addHours(5),
            'description' => fake()->sentence(),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }
}
