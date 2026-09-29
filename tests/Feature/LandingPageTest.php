<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\TimeSlot;
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

test('visitor can see active trainers list with contact button on landing page', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Alex']);
    $trainerUser->assignRole('Member');
    $trainer = Member::factory()->trainer()->create([
        'user_id' => $trainerUser->id,
        'phone' => '081234567890',
        'specialization' => 'Bodybuilding Specialist',
        'trainer_status' => 'active',
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Coach Alex')
        ->assertSee('Bodybuilding Specialist')
        ->assertSee('Hubungi / Chat via WhatsApp');
});
