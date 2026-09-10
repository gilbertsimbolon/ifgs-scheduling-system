<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('reset password screen can be rendered with sneat elements, branding, and indonesian labels', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $token = Password::createToken($user);

    $response = $this->get(route('password.reset', [
        'token' => $token,
        'email' => 'member@ifgs.test',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Indo Fitness Gym Sport®');
    $response->assertSee('Atur Ulang Kata Sandi');
    $response->assertSee('member@ifgs.test');
    $response->assertSee('Kata Sandi Baru');
    $response->assertSee('Konfirmasi Kata Sandi Baru');
    $response->assertSee('Kembali ke Login');
    $response->assertSee('authentication-basic');
});

test('authenticated users cannot visit reset password screen and get redirected', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $token = Password::createToken($user);

    $response = $this->actingAs($user)->get(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ]));

    $response->assertRedirect('/');
});

test('password can be reset with valid token and user can login with new password', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'password' => Hash::make('oldpassword123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Member');

    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'member@ifgs.test',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();

    // Verify user can login with the new password
    $loginResponse = $this->post(route('login.submit'), [
        'email' => 'member@ifgs.test',
        'password' => 'newpassword123',
    ]);

    $loginResponse->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('password reset fails with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'password' => Hash::make('oldpassword123'),
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->from(route('password.reset', ['token' => 'invalid-token', 'email' => $user->email]))
        ->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => 'member@ifgs.test',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'invalid-token', 'email' => $user->email]));
    $response->assertSessionHasErrors(['email' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.']);
    $response->assertSessionHas('error', 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.');

    $user->refresh();
    expect(Hash::check('oldpassword123', $user->password))->toBeTrue();
});

test('password reset fails when password confirmation does not match', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $token = Password::createToken($user);

    $response = $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.update'), [
            'token' => $token,
            'email' => 'member@ifgs.test',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => $token, 'email' => $user->email]));
    $response->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi tidak cocok.']);
});

test('password reset fails when password is less than 8 characters', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $token = Password::createToken($user);

    $response = $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.update'), [
            'token' => $token,
            'email' => 'member@ifgs.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => $token, 'email' => $user->email]));
    $response->assertSessionHasErrors(['password' => 'Kata sandi minimal 8 karakter.']);
});

test('password reset notification email renders themed view with logo and brand name', function () {
    $user = User::factory()->create([
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $token = 'test-reset-token';
    $notification = new ResetPasswordNotification($token);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Permintaan Reset Kata Sandi - Indo Fitness Gym Sport®');
    expect($mailMessage->view)->toBe('emails.reset-password');
    expect($mailMessage->viewData['user']->id)->toBe($user->id);
    expect($mailMessage->viewData['resetUrl'])->toContain($token);
    expect($mailMessage->viewData['resetUrl'])->toContain(urlencode('gilbert@ifgs.test'));

    // Render the view to ensure no Blade compile or template errors
    $renderedHtml = view($mailMessage->view, $mailMessage->viewData)->render();
    expect($renderedHtml)->toContain('Indo Fitness Gym Sport®');
    expect($renderedHtml)->toContain('Atur Ulang Kata Sandi');
    expect($renderedHtml)->toContain('Gilbert Simbolon');
    expect($renderedHtml)->toContain($mailMessage->viewData['resetUrl']);
});
