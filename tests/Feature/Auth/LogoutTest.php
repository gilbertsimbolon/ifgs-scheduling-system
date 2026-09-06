<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authenticated user can logout via post request and is redirected to login', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success', 'Anda berhasil keluar dari akun.');
});

test('guest cannot access logout route and is redirected to login', function () {
    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('logout cannot be accessed via get request and returns 405 method not allowed', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get('/logout');

    $response->assertStatus(405);
});

test('navbar renders logout form with post method and csrf token for authenticated user', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Admin/Manager');

    $response = $this->actingAs($user)->get(route('pengguna.index'));

    $response->assertStatus(200);
    $response->assertSee(route('logout'));
    $response->assertSee('id="navbar-logout-form"', false);
    $response->assertSee('Keluar');
});

test('user cannot perform authenticated actions after logging out', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();

    // Trying to logout again as guest must be redirected
    $response = $this->post(route('logout'));
    $response->assertRedirect(route('login'));
});
