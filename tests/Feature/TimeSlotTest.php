<?php

use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\User;
use Database\Seeders\TimeSlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('admin can view time slots index table', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    TimeSlot::factory()->create([
        'name' => 'Fitness (08:00 - 20:00)',
        'category' => TimeSlot::CATEGORY_FITNESS,
        'days' => 'Senin - Sabtu',
    ]);

    $this->actingAs($admin)
        ->get(route('time-slots.index'))
        ->assertOk()
        ->assertSee('Jadwal Operasional')
        ->assertSee('Fitness')
        ->assertSee('Fitness (08:00 - 20:00)');
});

test('/time_slots route alias redirects to /time-slots', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $this->actingAs($admin)
        ->get('/time_slots')
        ->assertRedirect('/time-slots');
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

test('admin can create a new time slot with category and operational days', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)
        ->post(route('time-slots.store'), [
            'name' => 'Sesi Zumba Spesial',
            'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
            'days' => 'Senin & Kamis',
            'start_time' => '19:00',
            'end_time' => '21:00',
            'capacity' => 25,
            'status' => 'active',
        ]);

    $response->assertRedirect(route('time-slots.index'));

    $this->assertDatabaseHas('time_slots', [
        'name' => 'Sesi Zumba Spesial',
        'category' => 'aerobic_zumba',
        'days' => 'Senin & Kamis',
        'start_time' => '19:00',
        'end_time' => '21:00',
        'capacity' => 25,
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
            'category' => TimeSlot::CATEGORY_FITNESS,
            'days' => 'Senin - Sabtu',
            'start_time' => '16:00',
            'end_time' => '18:00',
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

test('TimeSlotSeeder populates official schedule matching IFGS poster', function () {
    $this->seed(TimeSlotSeeder::class);

    expect(TimeSlot::where('category', TimeSlot::CATEGORY_FITNESS)->count())->toBe(1);
    expect(TimeSlot::where('category', TimeSlot::CATEGORY_AEROBIC_ZUMBA)->count())->toBe(1);

    $fitness = TimeSlot::where('name', 'Fitness (08:00 - 20:00)')->first();
    expect($fitness)->not->toBeNull();
    expect($fitness->start_time)->toBe('08:00');
    expect($fitness->end_time)->toBe('20:00');
    expect($fitness->days)->toBe('Senin - Sabtu');
    expect($fitness->capacity)->toBe(100);

    $zumba = TimeSlot::where('name', 'Aerobic & Zumba (19:00 - 21:00)')->first();
    expect($zumba)->not->toBeNull();
    expect($zumba->start_time)->toBe('19:00');
    expect($zumba->end_time)->toBe('21:00');
    expect($zumba->days)->toBe('Senin & Kamis');
    expect($zumba->capacity)->toBe(30);
});
