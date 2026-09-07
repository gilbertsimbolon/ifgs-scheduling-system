<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authorized user with Admin/Manager role can access /member', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Daftar Member');
    $response->assertSee('Home /');
    $response->assertSee('Kelola data member yang terdaftar pada sistem.');
});

test('authorized user with Kasir role can access /member', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($kasir)->get(route('member.index'));

    $response->assertStatus(200);
});

test('unauthorized user with Member role cannot access /member', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $response = $this->actingAs($memberUser)->get(route('member.index'));

    $response->assertStatus(403);
});

test('unauthenticated guest cannot access /member and is redirected to login', function () {
    $response = $this->get(route('member.index'));

    $response->assertRedirect(route('login'));
});

test('member list displays member_code, user name, email, phone, and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0001',
        'phone' => '081234567890',
    ]);

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('IFGS-202609-0001');
    $response->assertSee('Budi Santoso');
    $response->assertSee('budi@ifgs.test');
    $response->assertSee('081234567890');
    $response->assertSee('Aktif');
});

test('member with null phone displays dash and does not cause error', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Siti Rahma',
        'email' => 'siti@ifgs.test',
    ]);

    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0002',
        'phone' => null,
    ]);

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Siti Rahma');
    $response->assertSee('IFGS-202609-0002');
    $response->assertSee('-');
});

test('member list with no data displays clean empty state without error', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Belum ada member');
    $response->assertSee('Belum ada member yang terdaftar pada sistem.');
    $response->assertSee('Tidak ada data member');
});

test('pagination works properly when members exceed per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    // Create 15 members
    Member::factory()->count(15)->create();

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Menampilkan 1–10 dari 15 member');

    // Page 2
    $responsePage2 = $this->actingAs($admin)->get(route('member.index', ['page' => 2]));
    $responsePage2->assertStatus(200);
    $responsePage2->assertSee('Menampilkan 11–15 dari 15 member');
});
