<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slots = [
            [
                'name' => 'Sesi Pagi 1',
                'start_time' => '07:00',
                'end_time' => '08:30',
                'capacity' => 15,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
            [
                'name' => 'Sesi Pagi 2',
                'start_time' => '08:30',
                'end_time' => '10:00',
                'capacity' => 15,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
            [
                'name' => 'Sesi Siang',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'capacity' => 15,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
            [
                'name' => 'Sesi Sore 1',
                'start_time' => '15:00',
                'end_time' => '16:30',
                'capacity' => 20,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
            [
                'name' => 'Sesi Sore 2',
                'start_time' => '16:30',
                'end_time' => '18:00',
                'capacity' => 20,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
            [
                'name' => 'Sesi Malam 1',
                'start_time' => '18:00',
                'end_time' => '19:30',
                'capacity' => 20,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
            [
                'name' => 'Sesi Malam 2',
                'start_time' => '19:30',
                'end_time' => '21:00',
                'capacity' => 15,
                'status' => TimeSlot::STATUS_ACTIVE,
            ],
        ];

        foreach ($slots as $slot) {
            TimeSlot::firstOrCreate(
                ['name' => $slot['name']],
                $slot
            );
        }
    }
}
