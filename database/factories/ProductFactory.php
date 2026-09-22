<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Paket Gym 1 Bulan '.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'price' => 150000,
            'duration_value' => 1,
            'duration_unit' => Product::DURATION_MONTH,
            'status' => Product::STATUS_ACTIVE,
        ];
    }

    /**
     * Indicate that the product is a daily visit package.
     */
    public function dailyVisit(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Paket Gym Harian Visit '.fake()->unique()->numberBetween(1, 9999),
            'price' => 25000,
            'duration_value' => 1,
            'duration_unit' => Product::DURATION_DAY,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_INACTIVE,
        ]);
    }
}
