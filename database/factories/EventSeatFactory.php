<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventSeat> */
class EventSeatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'code' => 'SEAT-'.fake()->unique()->numberBetween(100, 9999),
            'label' => 'Kursi No '.fake()->numberBetween(1, 100),
            'zone' => fake()->randomElement(['VIP Lounge', 'Tribune Utara', 'Festival', null]),
            'status' => 'active',
        ];
    }
}