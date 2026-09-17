<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\TimeSlot;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainerBooking>
 */
class TrainerBookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'booking_code' => TrainerBooking::generateBookingCode(),
            'trainer_id' => Trainer::factory(),
            'member_id' => Member::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'session_date' => now()->addDays(fake()->numberBetween(1, 14))->toDateString(),
            'status' => TrainerBooking::STATUS_PENDING,
            'training_focus' => fake()->randomElement(['Fat Loss & Kardio', 'Pembentukan Otot Dada & Triceps', 'Kekuatan Inti & Core', 'Zumba & Aerobik', 'Program Kebugaran Umum']),
            'notes' => fake()->sentence(),
            'rejection_reason' => null,
            'approved_at' => null,
            'completed_at' => null,
        ];
    }
}
