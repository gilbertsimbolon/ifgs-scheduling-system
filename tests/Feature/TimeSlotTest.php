<?php

use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('admin can view time slots index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    TimeSlot::factory()->create(['name' => 'Sesi Pagi']);

    $this->actingAs($admin)
        ->get(route('time-slots.index'))
        ->assertOk()
        ->assertSee('Sesi Pagi');
});

test('non-admin cannot access time slots management', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)
        ->get(route('time-slots.index'))
        ->assertForbidden();

    $member = User::factory()->create();
    $member->assignRole('Member');

    $this->actingAs($member)
        ->get(route('time-slots.index'))
        ->assertForbidden();
});

test('admin can create a new time slot', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)
        ->post(route('time-slots.store'), [
            'name' => 'Sesi Pagi Khusus',
            'start_time' => '07:00',
            'end_time' => '08:30',
            'capacity' => 20,
            'status' => 'active',
        ]);

    $response->assertRedirect(route('time-slots.index'));

    $this->assertDatabaseHas('time_slots', [
        'name' => 'Sesi Pagi Khusus',
        'start_time' => '07:00',
        'end_time' => '08:30',
        'capacity' => 20,
        'status' => 'active',
    ]);
});

test('admin can update a time slot', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $slot = TimeSlot::factory()->create([
        'name' => 'Sesi Sore',
        'capacity' => 15,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('time-slots.update', $slot), [
            'name' => 'Sesi Sore Diperbarui',
            'start_time' => '16:00',
            'end_time' => '17:30',
            'capacity' => 25,
            'status' => 'active',
        ]);

    $response->assertRedirect(route('time-slots.index'));

    $this->assertDatabaseHas('time_slots', [
        'id' => $slot->id,
        'name' => 'Sesi Sore Diperbarui',
        'capacity' => 25,
    ]);
});

test('admin can toggle time slot status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $slot = TimeSlot::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)
        ->patch(route('time-slots.toggle-status', $slot));

    $response->assertRedirect(route('time-slots.index'));
    expect($slot->fresh()->status)->toBe('inactive');
});

test('cannot delete time slot if it has associated reservations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $slot = TimeSlot::factory()->create();
    Reservation::factory()->create(['time_slot_id' => $slot->id]);

    $response = $this->actingAs($admin)
        ->delete(route('time-slots.destroy', $slot));

    $response->assertRedirect(route('time-slots.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('time_slots', ['id' => $slot->id]);
});
