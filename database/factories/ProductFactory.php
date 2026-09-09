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
        $packages = [
            ['name' => 'Paket Gym Harian', 'duration_value' => 1, 'duration_unit' => Product::DURATION_DAY, 'price' => 25000],
            ['name' => 'Paket Gym 1 Bulan', 'duration_value' => 1, 'duration_unit' => Product::DURATION_MONTH, 'price' => 150000],
            ['name' => 'Paket Gym 3 Bulan', 'duration_value' => 3, 'duration_unit' => Product::DURATION_MONTH, 'price' => 400000],
            ['name' => 'Paket Gym 1 Tahun', 'duration_value' => 1, 'duration_unit' => Product::DURATION_YEAR, 'price' => 1400000],
            ['name' => 'Paket Zumba 1 Bulan', 'duration_value' => 1, 'duration_unit' => Product::DURATION_MONTH, 'price' => 175000],
        ];

        $package = fake()->randomElement($packages);

        return [
            'name' => $package['name'].' '.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'price' => $package['price'],
            'duration_value' => $package['duration_value'],
            'duration_unit' => $package['duration_unit'],
            'status' => Product::STATUS_ACTIVE,
        ];
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
