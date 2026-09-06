<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('forgot password screen can be rendered with sneat elements and indonesian labels', function () {
    $response = $this->get(route('password.request'));

    $response->assertStatus(200);
    $response->assertSee('IFGS');
    $response->assertSee('Lupa Kata Sandi? 🔒');
    $response->assertSee('Masukkan email yang terdaftar');
    $response->assertSee('Kirim Link Reset Password');
    $response->assertSee('Kembali ke Login');
    $response->assertSee('authentication-basic');
});

test('authenticated users cannot visit forgot password screen and get redirected', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('password.request'));

    $response->assertRedirect('/');
});

test('password reset link can be requested by registered user with notification sent and safe response', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'member@ifgs.test',
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHas('status', 'Jika email tersebut terdaftar, kami telah mengirimkan tautan untuk mengatur ulang kata sandi Anda.');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('unregistered email receives identical safe response without leaking user existence', function () {
    Notification::fake();

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'unregistered@ifgs.test',
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHas('status', 'Jika email tersebut terdaftar, kami telah mengirimkan tautan untuk mengatur ulang kata sandi Anda.');

    Notification::assertNothingSent();
});

test('email is required for forgot password request', function () {
    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => '',
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHasErrors(['email' => 'Email wajib diisi.']);
});

test('email must be a valid email format for forgot password request', function () {
    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'not-an-email',
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHasErrors(['email' => 'Email harus berupa alamat email yang valid.']);
});

test('login screen contains link to forgot password route', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee(route('password.request'));
    $response->assertSee('Lupa Kata Sandi?');
});
