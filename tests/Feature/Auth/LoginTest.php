<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('login screen can be rendered and contains sneat elements and indonesian labels', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Indo Fitness Gym Sport');
    $response->assertSee('IFGS');
    $response->assertSee('Selamat Datang di IFGS! 👋');
    $response->assertSee('Silakan masuk ke akun Anda untuk melanjutkan');
    $response->assertSee('Email');
    $response->assertSee('Kata Sandi');
    $response->assertSee('Ingat Saya');
    $response->assertSee('Masuk');
    $response->assertSee('Lupa Kata Sandi?');
    $response->assertSee('Daftar');
    $response->assertSee('authentication-basic');
    $response->assertSee('form-password-toggle');
});

test('authenticated users cannot visit login screen and get redirected', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect('/');
});

test('active users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'email' => 'active@ifgs.test',
        'password' => Hash::make('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Admin/Manager');

    $response = $this->post(route('login'), [
        'email' => 'active@ifgs.test',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('pengguna.index'));
});

test('member role users are redirected appropriately upon authentication', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'password' => Hash::make('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Member');

    $response = $this->post(route('login'), [
        'email' => 'member@ifgs.test',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(url('/'));
});

test('users can authenticate with remember me option', function () {
    $user = User::factory()->create([
        'email' => 'remember@ifgs.test',
        'password' => Hash::make('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'remember@ifgs.test',
        'password' => 'password123',
        'remember' => '1',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('pengguna.index'));
    $this->assertNotNull($user->fresh()->remember_token);
});

test('inactive users cannot authenticate and receive safe alert message', function () {
    $user = User::factory()->create([
        'email' => 'inactive@ifgs.test',
        'password' => Hash::make('password123'),
        'status' => User::STATUS_INACTIVE,
    ]);

    $response = $this->from(route('login'))->post(route('login'), [
        'email' => 'inactive@ifgs.test',
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Akun Anda tidak aktif. Silakan hubungi pengelola.');
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'user@ifgs.test',
        'password' => Hash::make('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->from(route('login'))->post(route('login'), [
        'email' => 'user@ifgs.test',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Email atau kata sandi yang Anda masukkan salah.');
});

test('users cannot authenticate with unregistered email', function () {
    $response = $this->from(route('login'))->post(route('login'), [
        'email' => 'nonexistent@ifgs.test',
        'password' => 'some-password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Email atau kata sandi yang Anda masukkan salah.');
});

test('email is required for login', function () {
    $response = $this->from(route('login'))->post(route('login'), [
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email' => 'Email wajib diisi.']);
    $this->assertGuest();
});

test('password is required for login', function () {
    $response = $this->from(route('login'))->post(route('login'), [
        'email' => 'test@ifgs.test',
        'password' => '',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['password' => 'Kata sandi wajib diisi.']);
    $this->assertGuest();
});

test('email must be valid format', function () {
    $response = $this->from(route('login'))->post(route('login'), [
        'email' => 'not-a-valid-email',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email' => 'Email harus berupa alamat email yang valid.']);
    $this->assertGuest();
});

test('spatie roles are preserved and accessible after authentication without user role column', function () {
    $admin = User::factory()->create([
        'email' => 'admin@ifgs.test',
        'password' => Hash::make('secret123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole('Admin/Manager');

    $this->post(route('login'), [
        'email' => 'admin@ifgs.test',
        'password' => 'secret123',
    ]);

    $authenticatedUser = Auth::user();
    $this->assertInstanceOf(User::class, $authenticatedUser);
    $this->assertTrue($authenticatedUser->hasRole('Admin/Manager'));
    $this->assertFalse($authenticatedUser->hasRole('Member'));
    $this->assertContains('Admin/Manager', $authenticatedUser->getRoleNames()->toArray());
});
