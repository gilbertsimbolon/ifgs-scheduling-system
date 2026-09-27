<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDuration;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Fitness',
                'description' => 'Akses seluruh area fitness Senin s/d Sabtu pukul 08.00-20.00 (Minggu & tanggal merah libur/tutup), locker, dan shower.',
                'status' => Product::STATUS_ACTIVE,
                'durations' => [
                    ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_DAY, 'price' => 25000],
                    ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 150000],
                    ['duration_value' => 2, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 250000],
                ],
            ],
            [
                'name' => 'Aerobic / Zumba',
                'description' => 'Akses kelas Aerobic / Zumba bersama instruktur berlisensi setiap Senin & Kamis pukul 19.00-21.00 WITA.',
                'status' => Product::STATUS_ACTIVE,
                'durations' => [
                    ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_DAY, 'price' => 25000],
                    ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 150000],
                    ['duration_value' => 2, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 250000],
                ],
            ],
            [
                'name' => 'Aerobic + Fitness',
                'description' => 'Akses gabungan Fitness (08.00-20.00) & Aerobic/Zumba (Senin & Kamis 19.00-21.00) selama 1 bulan.',
                'status' => Product::STATUS_INACTIVE,
                'durations' => [
                    ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 250000],
                ],
            ],
        ];

        foreach ($packages as $pkg) {
            $product = Product::updateOrCreate(
                ['name' => $pkg['name']],
                [
                    'description' => $pkg['description'],
                    'status' => $pkg['status'],
                ]
            );

            foreach ($pkg['durations'] as $dur) {
                $product->durations()->updateOrCreate(
                    [
                        'duration_value' => $dur['duration_value'],
                        'duration_unit' => $dur['duration_unit'],
                    ],
                    [
                        'price' => $dur['price'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
