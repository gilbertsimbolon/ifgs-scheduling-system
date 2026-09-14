<?php

use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Services\GreedySchedulingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('greedy scheduling selects slot with least occupancy to balance load', function () {
    $service = new GreedySchedulingService;
    $date = Carbon::today()->format('Y-m-d');

    // Buat dua time slot
    $slot1 = TimeSlot::factory()->create([
        'name' => 'Sesi Pagi',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'capacity' => 10,
        'status' => 'active',
    ]);

    $slot2 = TimeSlot::factory()->create([
        'name' => 'Sesi Sore',
        'start_time' => '16:00',
        'end_time' => '17:30',
        'capacity' => 10,
        'status' => 'active',
    ]);

    // Beri beban pada slot1 (sudah ada 3 jadwal)
    Schedule::factory()->count(3)->create([
        'time_slot_id' => $slot1->id,
        'scheduled_date' => $date,
        'status' => 'scheduled',
    ]);

    // Slot2 baru ada 1 jadwal
    Schedule::factory()->create([
        'time_slot_id' => $slot2->id,
        'scheduled_date' => $date,
        'status' => 'scheduled',
    ]);

    // Buat reservasi baru
    $reservation = Reservation::factory()->create([
        'visit_date' => $date,
        'status' => 'pending',
    ]);

    $result = $service->scheduleReservation($reservation);

    expect($result['success'])->toBeTrue();
    expect($result['time_slot']->id)->toBe($slot2->id); // Slot2 dipilih karena okupansi lebih rendah
    expect($reservation->fresh()->time_slot_id)->toBe($slot2->id);
    expect($reservation->fresh()->status)->toBe('scheduled');
});

test('greedy scheduling uses deterministic tie-breaker (earliest start_time) when occupancy is equal', function () {
    $service = new GreedySchedulingService;
    $date = Carbon::today()->format('Y-m-d');

    $slotSore = TimeSlot::factory()->create([
        'name' => 'Sesi Sore',
        'start_time' => '16:00',
        'end_time' => '17:30',
        'capacity' => 10,
        'status' => 'active',
    ]);

    $slotPagi = TimeSlot::factory()->create([
        'name' => 'Sesi Pagi',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'capacity' => 10,
        'status' => 'active',
    ]);

    // Kedua slot okupansinya 0
    $reservation = Reservation::factory()->create([
        'visit_date' => $date,
        'status' => 'pending',
    ]);

    $result = $service->scheduleReservation($reservation);

    expect($result['success'])->toBeTrue();
    // Tie-breaker memilih start_time 08:00 (slotPagi)
    expect($result['time_slot']->id)->toBe($slotPagi->id);
});

test('greedy scheduling enforces capacity constraint and rejects when all slots are full', function () {
    $service = new GreedySchedulingService;
    $date = Carbon::today()->format('Y-m-d');

    $slot = TimeSlot::factory()->create([
        'capacity' => 2,
        'status' => 'active',
    ]);

    // Isi penuh slot
    Schedule::factory()->count(2)->create([
        'time_slot_id' => $slot->id,
        'scheduled_date' => $date,
        'status' => 'scheduled',
    ]);

    $reservation = Reservation::factory()->create([
        'visit_date' => $date,
        'status' => 'pending',
    ]);

    $result = $service->scheduleReservation($reservation);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('penuh');
    expect($reservation->fresh()->status)->toBe('pending');
});

test('greedy batch optimization balances multiple pending reservations across slots', function () {
    $service = new GreedySchedulingService;
    $date = Carbon::today()->format('Y-m-d');

    $slotA = TimeSlot::factory()->create([
        'name' => 'Slot A',
        'start_time' => '08:00',
        'end_time' => '09:30',
        'capacity' => 5,
        'status' => 'active',
    ]);

    $slotB = TimeSlot::factory()->create([
        'name' => 'Slot B',
        'start_time' => '10:00',
        'end_time' => '11:30',
        'capacity' => 5,
        'status' => 'active',
    ]);

    // Buat 6 reservasi pending
    Reservation::factory()->count(6)->create([
        'visit_date' => $date,
        'status' => 'pending',
    ]);

    $result = $service->optimizeDate($date);

    expect($result['total_pending'])->toBe(6);
    expect($result['scheduled_count'])->toBe(6);
    expect($result['failed_count'])->toBe(0);

    // Keduanya harus terdistribusi seimbang: masing-masing 3 reservasi!
    $countA = Schedule::where('time_slot_id', $slotA->id)->whereDate('scheduled_date', $date)->count();
    $countB = Schedule::where('time_slot_id', $slotB->id)->whereDate('scheduled_date', $date)->count();

    expect($countA)->toBe(3);
    expect($countB)->toBe(3);
});
