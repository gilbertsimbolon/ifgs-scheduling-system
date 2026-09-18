<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\Product;
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

test('member can view reservations index and see own reservations only', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('Member');
    $member1 = Member::factory()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $user2->assignRole('Member');
    $member2 = Member::factory()->create(['user_id' => $user2->id]);

    $res1 = Reservation::factory()->create([
        'member_id' => $member1->id,
        'code' => 'RSV-20260911-0001',
    ]);
    $res2 = Reservation::factory()->create([
        'member_id' => $member2->id,
        'code' => 'RSV-20260911-0002',
    ]);

    $this->actingAs($user1)
        ->get(route('reservations.index'))
        ->assertOk()
        ->assertSee('RSV-20260911-0001')
        ->assertDontSee('RSV-20260911-0002');
});

test('member with active membership can make a reservation and get scheduled automatically via greedy', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $product = Product::factory()->create();
    $membership = Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'status' => 'active',
        'start_date' => Carbon::today()->subDays(5)->format('Y-m-d'),
        'end_date' => Carbon::today()->addDays(25)->format('Y-m-d'),
    ]);

    $slot = TimeSlot::factory()->create([
        'capacity' => 10,
        'status' => 'active',
    ]);

    $visitDate = Carbon::today()->format('Y-m-d');

    $response = $this->actingAs($user)
        ->post(route('reservations.store'), [
            'visit_date' => $visitDate,
            'notes' => 'Latihan kardio',
        ]);

    $response->assertRedirect(route('reservations.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'time_slot_id' => $slot->id,
        'status' => 'scheduled',
    ]);

    expect(Schedule::where('member_id', $member->id)
        ->where('time_slot_id', $slot->id)
        ->whereDate('scheduled_date', $visitDate)
        ->where('status', 'scheduled')
        ->exists())->toBeTrue();
});

test('reservation is rejected if member has no active membership on the visit date', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    TimeSlot::factory()->create(['status' => 'active']);

    $response = $this->actingAs($user)
        ->post(route('reservations.store'), [
            'visit_date' => Carbon::today()->format('Y-m-d'),
        ]);

    $response->assertSessionHas('error');
    $this->assertDatabaseCount('reservations', 0);
});

test('member cannot make duplicate reservation on the same date', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $membership = Membership::factory()->create([
        'member_id' => $member->id,
        'status' => 'active',
        'start_date' => Carbon::today()->subDays(2)->format('Y-m-d'),
        'end_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
    ]);

    TimeSlot::factory()->create(['status' => 'active']);

    $visitDate = Carbon::today()->format('Y-m-d');

    // First reservation
    $this->actingAs($user)->post(route('reservations.store'), ['visit_date' => $visitDate]);
    expect(Reservation::count())->toBe(1);

    // Second reservation on same date
    $response = $this->actingAs($user)->post(route('reservations.store'), ['visit_date' => $visitDate]);
    $response->assertSessionHas('error');
    expect(Reservation::count())->toBe(1);
});

test('member can cancel their own pending or scheduled reservation', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $slot = TimeSlot::factory()->create();
    $reservation = Reservation::factory()->create([
        'member_id' => $member->id,
        'time_slot_id' => $slot->id,
        'status' => 'scheduled',
        'visit_date' => Carbon::today()->addDays(1)->format('Y-m-d'),
    ]);

    $response = $this->actingAs($user)
        ->patch(route('reservations.cancel', $reservation));

    $response->assertRedirect(route('reservations.index'));
    expect($reservation->fresh()->status)->toBe('cancelled');
});

test('available slots endpoint returns capacity data for given date', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');

    $slot = TimeSlot::factory()->create([
        'name' => 'Sesi Pagi',
        'capacity' => 15,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('reservations.available-slots', ['date' => Carbon::today()->format('Y-m-d')]));

    $response->assertOk()
        ->assertJsonStructure([
            'date',
            'slots' => [
                '*' => ['id', 'name', 'time_range', 'capacity', 'occupied', 'remaining', 'is_full'],
            ],
        ]);
});

test('member cannot make reservation on Sunday because gym is closed', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $product = Product::factory()->create();
    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'start_date' => Carbon::now()->subDays(5),
        'end_date' => Carbon::now()->addDays(30),
        'status' => Membership::STATUS_ACTIVE,
    ]);

    $nextSunday = Carbon::now()->next(Carbon::SUNDAY)->format('Y-m-d');

    $response = $this->actingAs($user)
        ->post(route('reservations.store'), [
            'visit_date' => $nextSunday,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Indo Fitness Gym Sport tutup pada hari Minggu sesuai ketentuan jadwal operasional.');
});

test('reservation table displays separated columns and Indonesian formatted date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $member = Member::factory()->create(['user_id' => $user->id, 'member_code' => 'MBR-9988']);
    $product = Product::factory()->create(['name' => 'VIP Unlimited']);
    $membership = Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'status' => 'active',
    ]);
    $timeSlot = TimeSlot::factory()->create([
        'name' => 'Fitness Pagi',
        'start_time' => '08:00',
        'end_time' => '22:00',
    ]);

    $visitDate = Carbon::parse('2026-08-10'); // Senin, 10 Agustus 2026
    $reservation = Reservation::factory()->create([
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'time_slot_id' => $timeSlot->id,
        'visit_date' => $visitDate,
        'code' => 'RSV-20260810-0001',
        'status' => 'scheduled',
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.index'));
    $response->assertOk();

    // Verify column headers
    $response->assertSee('<th>Member</th>', false);
    $response->assertSee('<th>Paket</th>', false);
    $response->assertSee('<th>Waktu</th>', false);
    $response->assertSee('<th>Jenis Kunjungan</th>', false);
    $response->assertSee('<th>Waktu Sesi</th>', false);

    // Verify plain text row data without background badges on Member/Paket/Waktu
    $response->assertSee('Budi Santoso');
    $response->assertSee('MBR-9988');
    $response->assertSee('VIP Unlimited');
    $response->assertSee($visitDate->locale('id')->translatedFormat('l, d F Y')); // Senin, 10 Agustus 2026
    $response->assertSee('Fitness Pagi');
    $response->assertSee('08:00 - 22:00');

    // Verify metric cards are on dashboard
    $dashResponse = $this->actingAs($admin)->get(route('dashboard'));
    $dashResponse->assertOk();
    $dashResponse->assertSee('Ringkasan Status Reservasi Sistem');
    $dashResponse->assertSee('Total Reservasi');
    $dashResponse->assertSee('Terjadwal (Optimal)');
});

test('reservation is rejected if chosen time slot does not match member active membership package', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $fitnessProduct = Product::factory()->create([
        'name' => 'Fitness 1 Bulan',
    ]);

    $membership = Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $fitnessProduct->id,
        'status' => 'active',
        'start_date' => Carbon::today()->subDays(5)->format('Y-m-d'),
        'end_date' => Carbon::today()->addDays(25)->format('Y-m-d'),
    ]);

    $zumbaSlot = TimeSlot::factory()->create([
        'name' => 'Aerobic & Zumba',
        'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
        'capacity' => 30,
        'status' => 'active',
    ]);

    $fitnessSlot = TimeSlot::factory()->create([
        'name' => 'Fitness',
        'category' => TimeSlot::CATEGORY_FITNESS,
        'capacity' => 100,
        'status' => 'active',
    ]);

    // Next Monday to ensure gym is open
    $visitDate = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

    // 1. Try to book Zumba with only a Fitness membership -> Must be rejected
    $rejectResponse = $this->actingAs($user)
        ->post(route('reservations.store'), [
            'visit_date' => $visitDate,
            'time_slot_id' => $zumbaSlot->id,
        ]);

    $rejectResponse->assertRedirect();
    $rejectResponse->assertSessionHas('error');
    expect(Reservation::count())->toBe(0);

    // 2. Try to book Fitness with a Fitness membership -> Must succeed
    $successResponse = $this->actingAs($user)
        ->post(route('reservations.store'), [
            'visit_date' => $visitDate,
            'time_slot_id' => $fitnessSlot->id,
        ]);

    $successResponse->assertRedirect(route('reservations.index'));
    $successResponse->assertSessionHas('success');
    expect(Reservation::count())->toBe(1);
    expect(Reservation::first()->time_slot_id)->toBe($fitnessSlot->id);
});
