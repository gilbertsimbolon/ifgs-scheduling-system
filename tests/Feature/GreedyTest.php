<?php

use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\GreedySchedulingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-09-18 10:00:00'); // Jumat (Gym buka)
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('admin can access greedy operational page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    TimeSlot::factory()->create([
        'name' => 'Fitness Sesi 1',
        'category' => 'fitness',
        'capacity' => 100,
        'reservation_quota' => 70,
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('greedy.index'))
        ->assertOk()
        ->assertSee('Algoritma Greedy')
        ->assertSee('Fitness Sesi 1')
        ->assertSee('Kuota Reservasi')
        ->assertSee('Cadangan Walk-in');
});

test('kasir can access greedy operational page', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)
        ->get(route('greedy.index'))
        ->assertOk();
});

test('member cannot access greedy operational page', function () {
    $member = User::factory()->create();
    $member->assignRole('Member');

    $this->actingAs($member)
        ->get(route('greedy.index'))
        ->assertForbidden();
});

test('admin can update time slot reservation quota', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $slot = TimeSlot::factory()->create([
        'name' => 'Fitness Main Slot',
        'capacity' => 100,
        'reservation_quota' => null,
    ]);

    expect($slot->effective_reservation_quota)->toBe(70); // Default 70%
    expect($slot->walkin_quota)->toBe(30);

    $response = $this->actingAs($admin)
        ->patch(route('greedy.update-quota', $slot), [
            'reservation_quota' => 60,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $slot->refresh();
    expect($slot->reservation_quota)->toBe(60);
    expect($slot->effective_reservation_quota)->toBe(60);
    expect($slot->walkin_quota)->toBe(40);
});

test('reservation quota cannot exceed physical capacity or be negative', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $slot = TimeSlot::factory()->create([
        'capacity' => 30,
    ]);

    // Melebihi kapasitas (31 > 30)
    $response = $this->actingAs($admin)
        ->patch(route('greedy.update-quota', $slot), [
            'reservation_quota' => 31,
        ]);

    $response->assertSessionHasErrors(['reservation_quota']);

    // Nilai negatif
    $responseNeg = $this->actingAs($admin)
        ->patch(route('greedy.update-quota', $slot), [
            'reservation_quota' => -5,
        ]);

    $responseNeg->assertSessionHasErrors(['reservation_quota']);
});

test('reservation is capped by reservation_quota leaving room for walk-ins', function () {
    $service = new GreedySchedulingService;
    $date = '2026-09-18';

    // Buat slot dengan kapasitas fisik 10, namun kuota reservasi disetel 2
    $slot = TimeSlot::factory()->create([
        'name' => 'Fitness Limited',
        'capacity' => 10,
        'reservation_quota' => 2,
        'status' => 'active',
    ]);

    // Beri 2 schedule (mencapai kuota reservasi)
    Schedule::factory()->count(2)->create([
        'time_slot_id' => $slot->id,
        'scheduled_date' => $date,
        'status' => 'scheduled',
    ]);

    // Reservasi ke-3 harus ditolak oleh Greedy karena kuota reservasi sudah penuh,
    // meskipun kapasitas fisik (10) masih ada 8 untuk walk-in!
    $res = Reservation::factory()->create([
        'visit_date' => $date,
        'status' => 'pending',
    ]);

    $result = $service->scheduleReservation($res);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('penuh');
    expect($res->fresh()->status)->toBe('pending');
});

test('greedy batch optimization allocates pending reservations within quota', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');
    $date = '2026-09-18';

    $slot = TimeSlot::factory()->create([
        'capacity' => 10,
        'reservation_quota' => 2,
        'status' => 'active',
    ]);

    // Buat 3 reservasi pending
    Reservation::factory()->count(3)->create([
        'visit_date' => $date,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('greedy.optimize'), [
            'date' => $date,
        ]);

    $response->assertRedirect(route('greedy.index', ['date' => $date]));
    $response->assertSessionHas('success');

    // 2 berhasil dijadwalkan, 1 gagal karena kuota reservasi hanya 2
    $scheduledCount = Schedule::where('time_slot_id', $slot->id)->whereDate('scheduled_date', $date)->count();
    expect($scheduledCount)->toBe(2);

    $pendingCount = Reservation::whereDate('visit_date', $date)->where('status', 'pending')->count();
    expect($pendingCount)->toBe(1);
});
