<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $endDate = $product->calculateEndDate($startDate->format('Y-m-d'));

        return [
            'member_id' => Member::factory(),
            'product_id' => $product->id,
            'payment_method_id' => PaymentMethod::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'price' => $product->price,
            'status' => Membership::STATUS_ACTIVE,
        ];
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
