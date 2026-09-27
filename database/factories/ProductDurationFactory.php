<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductDuration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductDuration>
 */
class ProductDurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'duration_value' => 1,
            'duration_unit' => ProductDuration::DURATION_MONTH,
            'price' => 150000,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the duration is 1 day visit.
     */
    public function dailyVisit(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_value' => 1,
            'duration_unit' => ProductDuration::DURATION_DAY,
            'price' => 25000,
        ]);
    }

    /**
     * Indicate that the duration is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
