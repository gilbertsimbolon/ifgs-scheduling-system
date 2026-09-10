<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'code' => Str::slug($name).'_'.fake()->unique()->numberBetween(10, 999),
            'status' => PaymentMethod::STATUS_ACTIVE,
        ];
    }

    /**
     * State for inactive payment method.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentMethod::STATUS_INACTIVE,
        ]);
    }
}
