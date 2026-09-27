<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductDuration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

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
            'name' => 'Paket Gym '.fake()->unique()->words(2, true).' '.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'status' => Product::STATUS_ACTIVE,
        ];
    }

    /**
     * Configure the factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->durations()->doesntExist()) {
                $product->durations()->create([
                    'duration_value' => 1,
                    'duration_unit' => ProductDuration::DURATION_MONTH,
                    'price' => 150000,
                    'is_active' => true,
                ]);
            }
        });
    }

    /**
     * Intercept deprecated columns passed directly to factory creation.
     */
    public function create($attributes = [], ?Model $parent = null)
    {
        $durationData = [];
        if (is_array($attributes)) {
            foreach (['price', 'duration_value', 'duration_unit'] as $col) {
                if (array_key_exists($col, $attributes)) {
                    $durationData[$col] = $attributes[$col];
                    unset($attributes[$col]);
                }
            }
        }

        $product = parent::create($attributes, $parent);

        if (! empty($durationData)) {
            $duration = $product->durations()->first();
            if ($duration) {
                $duration->update([
                    'duration_value' => $durationData['duration_value'] ?? $duration->duration_value,
                    'duration_unit' => $durationData['duration_unit'] ?? $duration->duration_unit,
                    'price' => $durationData['price'] ?? $duration->price,
                ]);
            } else {
                $product->durations()->create([
                    'duration_value' => $durationData['duration_value'] ?? 1,
                    'duration_unit' => $durationData['duration_unit'] ?? ProductDuration::DURATION_MONTH,
                    'price' => $durationData['price'] ?? 150000,
                    'is_active' => true,
                ]);
            }
            $product->unsetRelation('durations');
            $product->unsetRelation('activeDurations');
        }

        return $product;
    }

    /**
     * Indicate that the product is a daily visit package.
     */
    public function dailyVisit(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product->durations()->delete();
            $product->durations()->create([
                'duration_value' => 1,
                'duration_unit' => ProductDuration::DURATION_DAY,
                'price' => 25000,
                'is_active' => true,
            ]);
        });
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
