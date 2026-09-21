<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('guest can view landing page with products, operational hours, and login links', function () {
    Product::factory()->create([
        'name' => 'Fitness 1 Bulan',
        'price' => 150000,
        'status' => Product::STATUS_ACTIVE,
    ]);

    TimeSlot::factory()->create([
        'name' => 'Sesi Pagi',
        'status' => 'active',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Indo Fitness Gym Sport®')
        ->assertSee('Paket Layanan')
        ->assertSee('Fitness 1 Bulan')
        ->assertSee('Rp 150.000')
        ->assertSee('Jam Buka Operasional')
        ->assertSee('Masuk')
        ->assertSee('Daftar Akun');
});

test('authenticated member sees membership status, QR absensi, and visit schedule on landing page', function () {
    $user = User::factory()->create(['name' => 'Michael Member']);
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id, 'member_code' => 'IFGS-M-TEST01']);

    $product = Product::factory()->create(['name' => 'Fitness Reguler', 'price' => 150000]);
    $paymentMethod = PaymentMethod::factory()->create(['name' => 'Transfer BCA']);

    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
        'status' => Membership::STATUS_ACTIVE,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
    ]);

    $slot = TimeSlot::factory()->create(['name' => 'Sesi Sore']);
    Schedule::factory()->create([
        'member_id' => $member->id,
        'time_slot_id' => $slot->id,
        'scheduled_date' => now()->toDateString(),
        'status' => 'scheduled',
    ]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee('Halo, Michael Member!')
        ->assertSee('PORTAL MEMBER IFGS')
        ->assertSee('Fitness Reguler')
        ->assertSee('IFGS-M-TEST01')
        ->assertSee('Reservasi Kunjungan')
        ->assertSee('QR Absensi')
        ->assertSee('Jadwal Kunjungan Saya')
        ->assertSee('Terjadwal');
});

test('authenticated trainer sees their assigned sessions and actions on landing page', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Alex']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create([
        'user_id' => $trainerUser->id,
        'specialization' => 'Bodybuilding Specialist',
    ]);

    $memberUser = User::factory()->create(['name' => 'David Client']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'training_focus' => 'Hypertrophy Chest & Back',
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $response = $this->actingAs($trainerUser)->get(route('home'));

    $response->assertOk()
        ->assertSee('PORTAL TRAINER IFGS')
        ->assertSee('Coach Alex')
        ->assertSee('Daftar Sesi Latihan Member')
        ->assertSee('David Client')
        ->assertSee('Hypertrophy Chest & Back')
        ->assertSee('Setujui')
        ->assertSee('Tolak');
});

test('trainer can approve session booking from landing page and remain on landing page', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Alex']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $memberUser = User::factory()->create(['name' => 'David Client']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $response = $this->actingAs($trainerUser)->patch(route('trainer-bookings.approve', $booking), [
        'redirect_to' => route('home'),
    ]);

    $response->assertRedirect(route('home'));
    expect($booking->fresh()->status)->toBe(TrainerBooking::STATUS_APPROVED);
});

test('trainer can reject session booking from landing page with reason', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Alex']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $memberUser = User::factory()->create(['name' => 'David Client']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $response = $this->actingAs($trainerUser)->patch(route('trainer-bookings.reject', $booking), [
        'reason' => 'Jadwal bertabrakan dengan agenda lain.',
        'redirect_to' => route('home'),
    ]);

    $response->assertRedirect(route('home'));
    expect($booking->fresh()->status)->toBe(TrainerBooking::STATUS_REJECTED);
    expect($booking->fresh()->rejection_reason)->toBe('Jadwal bertabrakan dengan agenda lain.');
});
