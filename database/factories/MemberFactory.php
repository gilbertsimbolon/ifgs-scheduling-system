<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
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
            'is_trainer' => false,
        ];
    }

    /**
     * State untuk menandai member sebagai trainer.
     */
    public function trainer(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trainer' => true,
            'specialization' => fake()->randomElement(['Fitness & Bodybuilding', 'Aerobic & Zumba', 'Strength & Conditioning', 'Fat Loss']),
            'bio' => fake()->sentence(10),
            'trainer_status' => 'active',
        ]);
    }
}
