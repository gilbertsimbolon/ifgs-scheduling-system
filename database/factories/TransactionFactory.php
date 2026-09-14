<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomElement([50000, 100000, 150000, 250000, 500000]);

        return [
            //
            'invoice_number' => 'TRX-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'member_id' => Member::factory(),
            'user_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'total_amount' => $amount,
            'paid_amount' => $amount,
            'change_amount' => 0.00,
            'status' => Transaction::STATUS_COMPLETED,
            'notes' => null,
        ];
    }
}
