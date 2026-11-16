<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Event> */
class ConcertFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Live';

        return [
            'tenant_id' => Tenant::factory(),
            'code' => strtoupper(Str::random(6)),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'venue' => 'Gelora '.fake()->word(),
            'city' => 'Jakarta',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonth()->addHours(5),
            'description' => fake()->sentence(),
            'status' => 'draft',
            'tax_rate' => 0.1000,
            'service_fee_rate' => 0.0200,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }
}