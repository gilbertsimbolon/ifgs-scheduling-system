<?php

namespace Database\Factories;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trainer>
 */
class TrainerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => fake()->phoneNumber(),
            'specialization' => fake()->randomElement(['Fitness & Bodybuilding', 'Aerobic & Zumba', 'Personal Trainer', 'Strength & Conditioning']),
            'bio' => fake()->sentence(),
            'status' => Trainer::STATUS_ACTIVE,
        ];
    }
}
