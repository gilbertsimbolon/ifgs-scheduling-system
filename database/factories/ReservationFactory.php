<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'RSV-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'member_id' => Member::factory(),
            'membership_id' => Membership::factory(),
            'visit_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_id' => null,
            'status' => Reservation::STATUS_PENDING,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Scheduled status state.
     */
    public function scheduled(?int $slotId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Reservation::STATUS_SCHEDULED,
            'time_slot_id' => $slotId ?? TimeSlot::factory(),
        ]);
    }

    /**
     * Pending status state.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Reservation::STATUS_PENDING,
            'time_slot_id' => null,
        ]);
    }
}
