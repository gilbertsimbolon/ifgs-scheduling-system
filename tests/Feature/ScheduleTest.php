<?php

use App\Models\Member;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('admin can view schedules monitoring page for a date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $slot = TimeSlot::factory()->create(['name' => 'Sesi Pagi Induk']);

    $this->actingAs($admin)
        ->get(route('schedules.index', ['date' => Carbon::today()->format('Y-m-d')]))
        ->assertOk()
        ->assertSee('Sesi Pagi Induk');
});

test('member can view their personal schedule history', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $schedule = Schedule::factory()->create([
        'member_id' => $member->id,
        'schedule_code' => 'SCH-TEST-0001',
    ]);

    $this->actingAs($user)
        ->get(route('schedules.index'))
        ->assertOk()
        ->assertSee('SCH-TEST-0001');
});

test('admin can trigger greedy batch optimization via POST /schedules/optimize', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $date = Carbon::today()->format('Y-m-d');
    TimeSlot::factory()->create(['capacity' => 10, 'status' => 'active']);
    Reservation::factory()->create(['visit_date' => $date, 'status' => 'pending']);

    $response = $this->actingAs($admin)
        ->post(route('schedules.optimize'), ['date' => $date]);

    $response->assertRedirect(route('schedules.index', ['date' => $date]));
    $response->assertSessionHas('success');
    expect(Schedule::whereDate('scheduled_date', $date)->count())->toBe(1);
});

test('admin can update schedule attendance status to attended', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $reservation = Reservation::factory()->create(['status' => 'scheduled']);
    $schedule = Schedule::factory()->create([
        'reservation_id' => $reservation->id,
        'status' => 'scheduled',
    ]);

    $response = $this->actingAs($admin)
        ->patch(route('schedules.update-status', $schedule), [
            'status' => 'attended',
        ]);

    $response->assertRedirect();
    expect($schedule->fresh()->status)->toBe('attended');
    expect($reservation->fresh()->status)->toBe('completed');
});
