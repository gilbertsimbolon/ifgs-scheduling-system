<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\Product;
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

test('admin dashboard displays membership metrics and monthly revenue', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $memberUser->id]);
    $product = Product::factory()->create(['price' => 150000]);

    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'price' => 150000,
        'status' => Membership::STATUS_ACTIVE,
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ringkasan Membership')
        ->assertSee('Total Transaksi')
        ->assertSee('Membership Aktif')
        ->assertSee('Kadaluarsa')
        ->assertSee('Pendapatan Bulan Ini')
        ->assertSee('Rp 150.000');
});
