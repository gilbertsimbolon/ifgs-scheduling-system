<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hour = fake()->unique()->numberBetween(6, 20);
        $start = sprintf('%02d:00', $hour);
        $end = sprintf('%02d:00', $hour + 1);

        return [
            'name' => "Sesi {$start} - {$end}",
            'start_time' => $start,
            'end_time' => $end,
            'capacity' => 10,
            'status' => TimeSlot::STATUS_ACTIVE,
        ];
    }

    /**
     * Indicate that time slot is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TimeSlot::STATUS_INACTIVE,
        ]);
    }
}
