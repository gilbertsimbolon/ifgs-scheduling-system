<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomElement([50000, 100000, 150000]);

        return [
            //
            'transaction_id' => Transaction::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'price' => $price,
            'quantity' => 1,
            'subtotal' => $price,
        ];
    }
}
