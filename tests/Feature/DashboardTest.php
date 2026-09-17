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
        ->assertSee('Rp 150.000')
        ->assertSee('Validasi Transaksi Membership')
        ->assertSee('Menunggu Validasi');
});

test('dashboard displays dynamic operational schedule cards', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    TimeSlot::factory()->create([
        'name' => 'Fitness (08:00 - 20:00)',
        'category' => TimeSlot::CATEGORY_FITNESS,
        'days' => 'Senin - Sabtu',
        'start_time' => '08:00',
        'end_time' => '20:00',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Jam Operasional Gym Resmi')
        ->assertSee('FITNESS')
        ->assertSee('08.00 - 20.00')
        ->assertSee('HARI LIBUR')
        ->assertSee('Minggu & Tanggal Merah', false);
});

test('adding a new operational service dynamically increases the cards on dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    TimeSlot::factory()->create([
        'name' => 'Fitness',
        'category' => TimeSlot::CATEGORY_FITNESS,
        'days' => 'Senin - Sabtu',
        'start_time' => '08:00',
        'end_time' => '20:00',
    ]);

    TimeSlot::factory()->create([
        'name' => 'Aerobic & Zumba',
        'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
        'days' => 'Senin & Kamis',
        'start_time' => '19:00',
        'end_time' => '21:00',
    ]);

    // Admin menambahkan 1 lagi layanan
    TimeSlot::factory()->create([
        'name' => 'Yoga & Pilates',
        'category' => 'yoga',
        'days' => 'Selasa & Jumat',
        'start_time' => '16:00',
        'end_time' => '18:00',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('FITNESS')
        ->assertSee('08.00 - 20.00')
        ->assertSee('AEROBIC & ZUMBA')
        ->assertSee('19.00 - 21.00')
        ->assertSee('YOGA & PILATES')
        ->assertSee('Selasa & Jumat')
        ->assertSee('16.00 - 18.00')
        ->assertSee('HARI LIBUR');
});
