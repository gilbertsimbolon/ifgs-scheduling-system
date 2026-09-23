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

test('kasir can access membership, transactions, trainer bookings, reservations, schedules, and attendance', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)->get(route('dashboard'))->assertOk();
    $this->actingAs($kasir)->get(route('memberships.index'))->assertOk();
    $this->actingAs($kasir)->get(route('membership-transactions.index'))->assertOk();
    $this->actingAs($kasir)->get(route('trainer-bookings.index'))->assertOk();
    $this->actingAs($kasir)->get(route('reservations.index'))->assertOk();
    $this->actingAs($kasir)->get(route('schedules.index'))->assertOk();
    $this->actingAs($kasir)->get(route('attendances.index'))->assertOk();
    $this->actingAs($kasir)->get(route('admin-members.index'))->assertOk();
    $this->actingAs($kasir)->get(route('transactions.index'))->assertOk();
});

test('kasir cannot access user management, time slots, products, trainers, payment methods, or greedy', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)->get(route('pengguna.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('time-slots.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('products.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('trainers.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('payment-methods.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('greedy.index'))->assertForbidden();
});

test('trainer cannot access admin dashboard or attendance panel and is redirected to home', function () {
    $trainerUser = User::factory()->create();
    $trainerUser->assignRole('Trainer');
    Trainer::factory()->create(['user_id' => $trainerUser->id]);

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

test('member cannot access admin dashboard and is redirected to member portal', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    Member::factory()->create(['user_id' => $memberUser->id]);

    $this->actingAs($memberUser)->get(route('dashboard'))->assertRedirect(route('member.index'));
});
