<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('register screen can be rendered with sneat elements and indonesian labels', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('IFGS');
    $response->assertSee('Pendaftaran Akun IFGS 🚀');
    $response->assertSee('Daftar sekarang untuk menjadwalkan kunjungan gym Anda!');
    $response->assertSee('Nama');
    $response->assertSee('Email');
    $response->assertSee('Kata Sandi');
    $response->assertSee('Konfirmasi Kata Sandi');
    $response->assertSee('Daftar');
    $response->assertSee('Sudah memiliki akun?');
    $response->assertSee('Masuk');
    $response->assertSee('form-password-toggle');
    $response->assertSee('authentication-basic');
});

test('authenticated users cannot visit register screen and get redirected', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('register'));

    $response->assertRedirect('/');
});

test('users can register with valid data and are assigned member role and active status', function () {
    $response = $this->post(route('register'), [
        'name' => 'Member Baru IFGS',
        'email' => 'memberbaru@ifgs.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success', 'Registrasi berhasil! Silakan masuk dengan akun Anda.');

    $this->assertDatabaseHas('users', [
        'name' => 'Member Baru IFGS',
        'email' => 'memberbaru@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $user = User::where('email', 'memberbaru@ifgs.test')->first();
    $this->assertNotNull($user);
    $this->assertNotEmpty($user->slug);
    $this->assertEquals('member-baru-ifgs', $user->slug);
    $this->assertTrue($user->hasRole('Member'));
    $this->assertFalse($user->hasRole('Admin/Manager'));
    $this->assertFalse($user->hasRole('Kasir'));
});

test('registered user password is saved as hash and not plaintext', function () {
    $this->post(route('register'), [
        'name' => 'Test Security User',
        'email' => 'security@ifgs.test',
        'password' => 'supersecret123',
        'password_confirmation' => 'supersecret123',
    ]);

    $user = User::where('email', 'security@ifgs.test')->first();
    $this->assertNotNull($user);
    $this->assertNotEquals('supersecret123', $user->password);
    $this->assertTrue(Hash::check('supersecret123', $user->password));
});

test('newly registered user can immediately login through the login flow', function () {
    $this->post(route('register'), [
        'name' => 'Member Siap Login',
        'email' => 'siaplogin@ifgs.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'siaplogin@ifgs.test')->first();

    $loginResponse = $this->post(route('login'), [
        'email' => 'siaplogin@ifgs.test',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $loginResponse->assertRedirect(url('/'));
});

test('name is required for register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => '',
        'email' => 'test@ifgs.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['name' => 'Nama wajib diisi.']);
    $this->assertDatabaseMissing('users', ['email' => 'test@ifgs.test']);
});

test('email is required for register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['email' => 'Email wajib diisi.']);
});

test('email must be a valid email address for register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['email' => 'Email harus berupa alamat email yang valid.']);
});

test('email must be unique for register', function () {
    User::factory()->create([
        'email' => 'existing@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Duplicate Email User',
        'email' => 'existing@ifgs.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['email' => 'Email sudah terdaftar.']);
});

test('password is required for register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@ifgs.test',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['password' => 'Kata sandi wajib diisi.']);
});

test('password must be at least 8 characters for register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@ifgs.test',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['password' => 'Kata sandi minimal 8 karakter.']);
});

test('password confirmation must match for register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@ifgs.test',
        'password' => 'password123',
        'password_confirmation' => 'differentpassword',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi tidak cocok.']);
});
