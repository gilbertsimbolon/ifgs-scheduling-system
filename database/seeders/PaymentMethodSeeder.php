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
                'type' => PaymentMethod::TYPE_CASH,
                'code' => 'cash',
                'account_number' => null,
                'account_name' => null,
                'qr_image' => null,
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'Transfer Bank',
                'type' => PaymentMethod::TYPE_BANK_TRANSFER,
                'code' => 'bank_transfer',
                'account_number' => '123-456-7890 (BCA)',
                'account_name' => 'Indo Fitness Gym',
                'qr_image' => null,
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'QRIS',
                'type' => PaymentMethod::TYPE_QRIS,
                'code' => 'qris',
                'account_number' => 'NMID: ID1024300928172',
                'account_name' => 'Indo Fitness Gym Sport',
                'qr_image' => 'img/qris-ifgs.svg',
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
