<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_code' => 'SCH-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'reservation_id' => function (array $attributes) {
                return Reservation::factory()->create([
                    'member_id' => $attributes['member_id'] ?? Member::factory(),
                    'time_slot_id' => $attributes['time_slot_id'] ?? null,
                    'visit_date' => $attributes['scheduled_date'] ?? now()->addDay()->format('Y-m-d'),
                    'status' => Reservation::STATUS_SCHEDULED,
                ])->id;
            },
            'member_id' => Member::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'status' => Schedule::STATUS_SCHEDULED,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
