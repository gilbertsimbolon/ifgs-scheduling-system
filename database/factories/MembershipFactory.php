<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductDuration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $product = Product::factory()->create();
        $duration = $product->activeDurations()->first() ?? $product->durations()->first() ?? $product->durations()->create([
            'duration_value' => 1,
            'duration_unit' => ProductDuration::DURATION_MONTH,
            'price' => 150000,
            'is_active' => true,
        ]);
        $endDate = $duration->calculateEndDate($startDate->format('Y-m-d'));

        return [
            'member_id' => Member::factory(),
            'product_id' => $product->id,
            'product_duration_id' => $duration->id,
            'payment_method_id' => PaymentMethod::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'price' => $duration->price,
            'status' => Membership::STATUS_ACTIVE,
        ];
    }

    /**
     * Create the model instance with fallback for price and duration.
     */
    public function create($attributes = [], ?Model $parent = null)
    {
        if (is_array($attributes) && isset($attributes['product_id'])) {
            $prod = Product::find($attributes['product_id']);
            if ($prod) {
                if (! isset($attributes['product_duration_id'])) {
                    $attributes['product_duration_id'] = $prod->activeDurations()->first()?->id ?? $prod->durations()->first()?->id;
                }
                if (! isset($attributes['price']) || $attributes['price'] === null) {
                    $attributes['price'] = $prod->min_price ?: 150000;
                }
            }
        }

        return parent::create($attributes, $parent);
    }

    /**
     * Indicate that the membership is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subMonths(2)->format('Y-m-d'),
            'end_date' => now()->subMonth()->format('Y-m-d'),
            'status' => Membership::STATUS_EXPIRED,
        ]);
    }

    /**
     * Indicate that the membership is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Membership::STATUS_CANCELLED,
        ]);
    }
}
