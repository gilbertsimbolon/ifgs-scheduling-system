<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'date' => today()->toDateString(),
            'check_in_at' => now(),
            'check_out_at' => null,
            'status' => Attendance::STATUS_CHECKED_IN,
            'scan_method' => 'camera',
            'notes' => null,
        ];
    }

    /**
     * State untuk presensi yang telah selesai (check-out).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_at' => now()->subHours(2),
            'check_out_at' => now(),
            'status' => Attendance::STATUS_COMPLETED,
        ]);
    }
}
