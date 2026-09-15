<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sesuai Ketentuan & Poster Resmi Indo Fitness Gym Sport:
     * 1. Fitness: Senin s/d Sabtu, Pukul 08.00 - 20.00
     *    - Akses Masuk Bebas (Tanpa batasan per jam, bukan 24 jam).
     *    - Tiket Visit berlaku sampai jam tutup gym pukul 20.00 pada hari yang sama.
     * 2. Aerobic / Zumba: Senin & Kamis, Pukul 19.00 - 21.00
     * 3. Hari Minggu & Tanggal Merah TUTUP.
     */
    public function run(): void
    {
        // 1. Jadwal Operasional Fitness (08:00 - 20:00)
        // Periksa apakah slot ID 1 sudah ada (misal direferensikan relasi jadwal/reservasi)
        $fitnessSlot = TimeSlot::find(1);
        if ($fitnessSlot) {
            $fitnessSlot->update([
                'name' => 'Fitness (08:00 - 20:00)',
                'category' => TimeSlot::CATEGORY_FITNESS,
                'days' => 'Senin - Sabtu',
                'start_time' => '08:00',
                'end_time' => '20:00',
                'capacity' => 100,
                'status' => TimeSlot::STATUS_ACTIVE,
            ]);
        } else {
            TimeSlot::updateOrCreate(
                ['name' => 'Fitness (08:00 - 20:00)'],
                [
                    'category' => TimeSlot::CATEGORY_FITNESS,
                    'days' => 'Senin - Sabtu',
                    'start_time' => '08:00',
                    'end_time' => '20:00',
                    'capacity' => 100,
                    'status' => TimeSlot::STATUS_ACTIVE,
                ]
            );
        }

        // 2. Jadwal Operasional Aerobic & Zumba (19:00 - 21:00)
        $zumbaSlot = TimeSlot::where('name', 'Sesi Aerobic & Zumba')
            ->orWhere('name', 'Aerobic & Zumba (19:00 - 21:00)')
            ->first();

        if ($zumbaSlot) {
            $zumbaSlot->update([
                'name' => 'Aerobic & Zumba (19:00 - 21:00)',
                'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
                'days' => 'Senin & Kamis',
                'start_time' => '19:00',
                'end_time' => '21:00',
                'capacity' => 30,
                'status' => TimeSlot::STATUS_ACTIVE,
            ]);
        } else {
            TimeSlot::updateOrCreate(
                ['name' => 'Aerobic & Zumba (19:00 - 21:00)'],
                [
                    'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
                    'days' => 'Senin & Kamis',
                    'start_time' => '19:00',
                    'end_time' => '21:00',
                    'capacity' => 30,
                    'status' => TimeSlot::STATUS_ACTIVE,
                ]
            );
        }

        // 3. Bersihkan slot-slot sesi per jam lama yang tidak memiliki riwayat reservasi atau jadwal
        TimeSlot::whereNotIn('name', [
            'Fitness (08:00 - 20:00)',
            'Aerobic & Zumba (19:00 - 21:00)',
        ])
            ->whereDoesntHave('schedules')
            ->whereDoesntHave('reservations')
            ->delete();
    }
}
