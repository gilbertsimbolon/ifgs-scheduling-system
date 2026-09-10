<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Tunai',
                'code' => 'cash',
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'Transfer Bank',
                'code' => 'bank_transfer',
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'QRIS',
                'code' => 'qris',
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'status' => $method['status'],
                ]
            );
        }
    }
}
