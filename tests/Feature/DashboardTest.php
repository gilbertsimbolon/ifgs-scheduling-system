<?php

use App\Models\Member;
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

test('unauthenticated guest is redirected to login when visiting /dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated admin can view gym dashboard with kpi metrics and time slots', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    TimeSlot::factory()->create(['name' => 'Sesi Pagi Utama']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Penjadwalan Gym IFGS')
        ->assertSee('Total Member')
        ->assertSee('Sesi Pagi Utama');
});

test('authenticated member can view personal dashboard', function () {
    $user = User::factory()->create(['name' => 'Gilbert Simbolon']);
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Halo, Gilbert Simbolon')
        ->assertSee('Paket Membership Anda');
});

test('authenticated user accessing root url sees welcome page with link to dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $this->actingAs($admin)
        ->get('/')
        ->assertOk()
        ->assertSee('Dashboard');
});
