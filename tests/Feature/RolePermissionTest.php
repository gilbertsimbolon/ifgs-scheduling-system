<?php

use App\Models\Member;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can access everything and perform CRUD operations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $this->actingAs($admin)->get(route('dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('pengguna.index'))->assertOk();
    $this->actingAs($admin)->get(route('time-slots.index'))->assertOk();
    $this->actingAs($admin)->get(route('products.index'))->assertOk();
    $this->actingAs($admin)->get(route('trainers.index'))->assertOk();
    $this->actingAs($admin)->get(route('payment-methods.index'))->assertOk();
    $this->actingAs($admin)->get(route('greedy.index'))->assertOk();
    $this->actingAs($admin)->get(route('memberships.index'))->assertOk();
    $this->actingAs($admin)->get(route('membership-transactions.index'))->assertOk();
    $this->actingAs($admin)->get(route('trainer-bookings.index'))->assertOk();
    $this->actingAs($admin)->get(route('reservations.index'))->assertOk();
    $this->actingAs($admin)->get(route('schedules.index'))->assertOk();
    $this->actingAs($admin)->get(route('attendances.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin-members.index'))->assertOk();
    $this->actingAs($admin)->get(route('transactions.index'))->assertOk();
});

test('kasir can access membership, transactions, reservations, schedules, and attendance', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)->get(route('dashboard'))->assertOk();
    $this->actingAs($kasir)->get(route('memberships.index'))->assertOk();
    $this->actingAs($kasir)->get(route('membership-transactions.index'))->assertOk();
    $this->actingAs($kasir)->get(route('reservations.index'))->assertOk();
    $this->actingAs($kasir)->get(route('schedules.index'))->assertOk();
    $this->actingAs($kasir)->get(route('attendances.index'))->assertOk();
    $this->actingAs($kasir)->get(route('admin-members.index'))->assertOk();
    $this->actingAs($kasir)->get(route('transactions.index'))->assertOk();
});

test('kasir cannot access user management, time slots, products, trainers, payment methods, greedy, or trainer bookings', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)->get(route('pengguna.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('time-slots.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('products.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('trainers.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('payment-methods.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('greedy.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('trainer-bookings.index'))->assertForbidden();
});

test('kasir cannot perform actions on trainer bookings', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $booking = TrainerBooking::factory()->create([
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $this->actingAs($kasir)->patch(route('trainer-bookings.approve', $booking))->assertForbidden();
    $this->actingAs($kasir)->patch(route('trainer-bookings.reject', $booking), ['reason' => 'Alasan'])->assertForbidden();
    $this->actingAs($kasir)->patch(route('trainer-bookings.complete', $booking))->assertForbidden();
});

test('trainer can access trainer bookings but cannot access dashboard or attendance panel and is redirected to home', function () {
    $trainerUser = User::factory()->create();
    $trainerUser->assignRole('Trainer');
    Trainer::factory()->create(['user_id' => $trainerUser->id]);

    // Trainer can access trainer bookings
    $this->actingAs($trainerUser)->get(route('trainer-bookings.index'))->assertOk();

    // Dashboard redirects trainer to landing page
    $this->actingAs($trainerUser)->get(route('dashboard'))->assertRedirect(route('home'));

    // Attendance panel is forbidden to trainer
    $this->actingAs($trainerUser)->get(route('attendances.index'))->assertForbidden();
});

test('trainer can only approve or reject their own assigned sessions', function () {
    $trainerUser1 = User::factory()->create();
    $trainerUser1->assignRole('Trainer');
    $trainer1 = Trainer::factory()->create(['user_id' => $trainerUser1->id]);

    $trainerUser2 = User::factory()->create();
    $trainerUser2->assignRole('Trainer');
    $trainer2 = Trainer::factory()->create(['user_id' => $trainerUser2->id]);

    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $bookingTrainer1 = TrainerBooking::factory()->create([
        'trainer_id' => $trainer1->id,
        'member_id' => $member->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $bookingTrainer2 = TrainerBooking::factory()->create([
        'trainer_id' => $trainer2->id,
        'member_id' => $member->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    // Trainer 1 can approve booking 1
    $this->actingAs($trainerUser1)
        ->patch(route('trainer-bookings.approve', $bookingTrainer1))
        ->assertRedirect(route('trainer-bookings.index'));

    expect($bookingTrainer1->fresh()->status)->toBe(TrainerBooking::STATUS_APPROVED);

    // Trainer 1 CANNOT approve booking 2 (belongs to Trainer 2)
    $this->actingAs($trainerUser1)
        ->patch(route('trainer-bookings.approve', $bookingTrainer2))
        ->assertForbidden();

    expect($bookingTrainer2->fresh()->status)->toBe(TrainerBooking::STATUS_PENDING);
});

test('member cannot access admin dashboard or trainer bookings', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $this->actingAs($memberUser)->get(route('dashboard'))->assertRedirect(route('member.index'));
    $this->actingAs($memberUser)->get(route('trainer-bookings.index'))->assertForbidden();
    $this->actingAs($memberUser)->patch(route('trainer-bookings.approve', $booking))->assertForbidden();
    $this->actingAs($memberUser)->patch(route('trainer-bookings.reject', $booking), ['reason' => 'Alasan'])->assertForbidden();
    $this->actingAs($memberUser)->patch(route('trainer-bookings.complete', $booking))->assertForbidden();
});

test('admin sees all sidebar menus with no duplicates', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertOk();

    $content = $response->getContent();

    // Verify all admin menus exist
    $response->assertSee('Dashboard');
    $response->assertSee('Pengguna');
    $response->assertSee('Data Member');
    $response->assertSee('Membership');
    $response->assertSee('Transaksi Membership');
    $response->assertSee('Paket Layanan');
    $response->assertSee('Trainer');
    $response->assertSee('Sesi Trainer');
    $response->assertSee('Metode Pembayaran');
    $response->assertSee('Jadwal Operasional');
    $response->assertSee('Reservasi');
    $response->assertSee('Kunjungan');
    $response->assertSee('Greedy');
    $response->assertSee('Check-in &amp; Check-out', false);

    // Verify NO DUPLICATES in sidebar menu-text / text-truncate
    expect(substr_count($content, '<div class="text-truncate">Membership</div>'))->toBe(1);
    expect(substr_count($content, '<div class="text-truncate">Transaksi Membership</div>'))->toBe(1);
    expect(substr_count($content, '<div class="text-truncate">Data Member</div>'))->toBe(1);
    expect(substr_count($content, '<div class="text-truncate">Pengguna</div>'))->toBe(1);
    expect(substr_count($content, '<div class="text-truncate">Sesi Trainer</div>'))->toBe(1);
});

test('kasir sees only permitted sidebar menus with no duplicates', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($kasir)->get(route('dashboard'));
    $response->assertOk();

    $content = $response->getContent();

    // Menus that Kasir SHOULD see
    $response->assertSee('Dashboard');
    $response->assertSee('Data Member');
    $response->assertSee('Membership');
    $response->assertSee('Transaksi Membership');
    $response->assertSee('Reservasi');
    $response->assertSee('Kunjungan');
    $response->assertSee('Check-in &amp; Check-out', false);

    // Menus that Kasir SHOULD NOT see in sidebar
    $response->assertDontSee('<div class="text-truncate">Pengguna</div>', false);
    $response->assertDontSee('<div class="text-truncate">Paket Layanan</div>', false);
    $response->assertDontSee('<div class="text-truncate">Trainer</div>', false);
    $response->assertDontSee('<div class="text-truncate">Sesi Trainer</div>', false);
    $response->assertDontSee('<div class="text-truncate">Metode Pembayaran</div>', false);
    $response->assertDontSee('<div class="text-truncate">Jadwal Operasional</div>', false);
    $response->assertDontSee('<div class="text-truncate">Greedy</div>', false);

    // Verify NO DUPLICATES
    expect(substr_count($content, '<div class="text-truncate">Membership</div>'))->toBe(1);
    expect(substr_count($content, '<div class="text-truncate">Transaksi Membership</div>'))->toBe(1);
});

test('trainer sees trainer bookings menu in sidebar when accessing trainer bookings', function () {
    $trainerUser = User::factory()->create();
    $trainerUser->assignRole('Trainer');
    Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $response = $this->actingAs($trainerUser)->get(route('trainer-bookings.index'));
    $response->assertOk();

    // Trainer sees Sesi Trainer
    $response->assertSee('<div class="text-truncate">Sesi Trainer</div>', false);
    // Trainer does not see other management / operational items
    $response->assertDontSee('<div class="text-truncate">Dashboard</div>', false);
    $response->assertDontSee('<div class="text-truncate">Pengguna</div>', false);
    $response->assertDontSee('<div class="text-truncate">Data Member</div>', false);
    $response->assertDontSee('<div class="text-truncate">Membership</div>', false);
    $response->assertDontSee('<div class="text-truncate">Transaksi Membership</div>', false);
    $response->assertDontSee('<div class="text-truncate">Paket Layanan</div>', false);
    $response->assertDontSee('<div class="text-truncate">Trainer</div>', false);
    $response->assertDontSee('<div class="text-truncate">Metode Pembayaran</div>', false);
    $response->assertDontSee('<div class="text-truncate">Jadwal Operasional</div>', false);
    $response->assertDontSee('<div class="text-truncate">Reservasi</div>', false);
    $response->assertDontSee('<div class="text-truncate">Kunjungan</div>', false);
    $response->assertDontSee('<div class="text-truncate">Greedy</div>', false);
    $response->assertDontSee('<div class="text-truncate">Check-in &amp; Check-out</div>', false);
});
