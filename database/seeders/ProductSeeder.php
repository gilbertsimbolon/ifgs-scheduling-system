<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Fitness 1 Bulan',
                'description' => 'Akses fitness Senin s/d Sabtu pukul 08.00-20.00 (Minggu & tanggal merah tutup)',
                'price' => 150000,
                'duration_value' => 1,
                'duration_unit' => Product::DURATION_MONTH,
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'name' => 'Fitness 2 Bulan',
                'description' => 'Akses fitness Senin s/d Sabtu pukul 08.00-20.00 (Minggu & tanggal merah tutup)',
                'price' => 250000,
                'duration_value' => 2,
                'duration_unit' => Product::DURATION_MONTH,
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'name' => 'Fitness Visit',
                'description' => 'Akses fitness 1 hari (berlaku sampai jam tutup gym pada hari yang sama)',
                'price' => 25000,
                'duration_value' => 1,
                'duration_unit' => Product::DURATION_DAY,
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'name' => 'Aerobic / Zumba 1 Bulan',
                'description' => 'Akses kelas Aerobic / Zumba Senin & Kamis pukul 19.00-21.00',
                'price' => 150000,
                'duration_value' => 1,
                'duration_unit' => Product::DURATION_MONTH,
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'name' => 'Aerobic / Zumba 2 Bulan',
                'description' => 'Akses kelas Aerobic / Zumba Senin & Kamis pukul 19.00-21.00',
                'price' => 250000,
                'duration_value' => 2,
                'duration_unit' => Product::DURATION_MONTH,
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'name' => 'Aerobic / Zumba Visit',
                'description' => 'Akses kelas Aerobic / Zumba 1 sesi kunjungan pada hari yang sama',
                'price' => 25000,
                'duration_value' => 1,
                'duration_unit' => Product::DURATION_DAY,
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'name' => 'Aerobic + Fitness',
                'description' => 'Akses gabungan Fitness (08.00-20.00) & Aerobic/Zumba (Senin & Kamis 19.00-21.00) selama 1 bulan',
                'price' => 250000,
                'duration_value' => 1,
                'duration_unit' => Product::DURATION_MONTH,
                'status' => Product::STATUS_ACTIVE,
            ],
        ];

        // Jika ada Paket GYM lama, update menjadi Fitness 1 Bulan agar riwayat transaksi tetap terjaga
        $oldGym = Product::where('name', 'like', '%Paket GYM%')->first();
        if ($oldGym) {
            $first = array_shift($products);
            $oldGym->update($first);
        }

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
