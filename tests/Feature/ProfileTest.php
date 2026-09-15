<?php

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Trainer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('unauthenticated guest cannot access /profile and is redirected to login', function () {
    $response = $this->get(route('profile.show'));

    $response->assertRedirect(route('login'));
});

test('authenticated member can access profile page and see QR Code, personal data, and password forms', function () {
    $user = User::factory()->create([
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
    ]);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-998877',
        'phone' => '081234567890',
    ]);

    $response = $this->actingAs($user)->get(route('profile.show'));

    $response->assertStatus(200);
    $response->assertSee('Profil Saya');
    $response->assertSee('Gilbert Simbolon');
    $response->assertSee('gilbert@ifgs.test');
    $response->assertSee('081234567890');
    $response->assertSee($user->qr_code);
    $response->assertSee('MBR-998877');
    $response->assertSee('modalQrCodeProfile');
    $response->assertSee('Aktivitas Kunjungan (Check-In dan Check-Out)');
    $response->assertSee('Check-In Kunjungan Gym');
    $response->assertSee('Check-Out Kunjungan Gym');
    $response->assertSee('Berhasil melakukan check-in pada hari ini');
    $response->assertSee('Perbarui Password');
    $response->assertSee('Simpan Data Diri');
});

test('authenticated admin can access profile page and see role badge and QR Code', function () {
    $admin = User::factory()->create([
        'name' => 'Super Administrator',
        'email' => 'admin@ifgs.test',
    ]);
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('profile.show'));

    $response->assertStatus(200);
    $response->assertSee('Super Administrator');
    $response->assertSee('Admin/Manager');
    $response->assertSee($admin->qr_code);
});

test('user can update personal data successfully and slug is regenerated if name changes', function () {
    $user = User::factory()->create([
        'name' => 'Nama Awal',
        'email' => 'awal@ifgs.test',
    ]);
    $user->assignRole('Member');
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-112233',
        'phone' => '0811111111',
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Nama Baru Lengkap',
        'email' => 'baru@ifgs.test',
        'phone' => '0822222222',
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHas('profile_success');

    $user->refresh();
    expect($user->name)->toBe('Nama Baru Lengkap')
        ->and($user->email)->toBe('baru@ifgs.test')
        ->and($user->slug)->toBe('nama-baru-lengkap')
        ->and($user->phone)->toBe('0822222222');

    $member->refresh();
    expect($member->phone)->toBe('0822222222');
});

test('user cannot update email to another users email', function () {
    $user1 = User::factory()->create(['email' => 'existing@ifgs.test']);
    $user2 = User::factory()->create(['email' => 'myemail@ifgs.test']);
    $user2->assignRole('Member');

    $response = $this->actingAs($user2)->put(route('profile.update'), [
        'name' => 'My Name',
        'email' => 'existing@ifgs.test',
        'phone' => '081234567890',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('trainer user updates phone synced to trainer profile', function () {
    $user = User::factory()->create(['name' => 'Trainer Joni']);
    $user->assignRole('Trainer');
    $trainer = Trainer::create([
        'user_id' => $user->id,
        'trainer_code' => 'TRN-099',
        'phone' => '0855555555',
        'status' => Trainer::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Trainer Joni Perkasa',
        'email' => $user->email,
        'phone' => '0877777777',
    ]);

    $response->assertRedirect(route('profile.show'));
    $trainer->refresh();
    expect($trainer->phone)->toBe('0877777777');
});

test('user can update password with valid current password and matching confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);
    $user->assignRole('Member');

    $response = $this->actingAs($user)->put(route('profile.password.update'), [
        'current_password' => 'oldpassword123',
        'password' => 'newsecretpassword456',
        'password_confirmation' => 'newsecretpassword456',
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHas('password_success');

    $user->refresh();
    expect(Hash::check('newsecretpassword456', $user->password))->toBeTrue();
});

test('password update fails when current password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);
    $user->assignRole('Member');

    $response = $this->actingAs($user)->put(route('profile.password.update'), [
        'current_password' => 'wrongpassword',
        'password' => 'newsecretpassword456',
        'password_confirmation' => 'newsecretpassword456',
    ]);

    $response->assertSessionHasErrors(['current_password']);
    $user->refresh();
    expect(Hash::check('oldpassword123', $user->password))->toBeTrue();
});

test('password update fails when confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);
    $user->assignRole('Member');

    $response = $this->actingAs($user)->put(route('profile.password.update'), [
        'current_password' => 'oldpassword123',
        'password' => 'newsecretpassword456',
        'password_confirmation' => 'differentpassword789',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('password update fails when new password is too short', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);
    $user->assignRole('Member');

    $response = $this->actingAs($user)->put(route('profile.password.update'), [
        'current_password' => 'oldpassword123',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('user can upload avatar and view it on profile', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->assignRole('Member');

    $file = UploadedFile::fake()->image('profile-photo.jpg', 200, 200);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $file,
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHas('profile_success');

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
    expect($user->avatar_url)->not->toBeNull();

    // Verify avatar renders on profile page
    $profileRes = $this->actingAs($user)->get(route('profile.show'));
    $profileRes->assertStatus(200);
    $profileRes->assertSee($user->avatar_url);
});

test('user can remove avatar', function () {
    Storage::fake('public');

    $fakePath = 'avatars/fake-avatar.jpg';
    Storage::disk('public')->put($fakePath, 'fake content');

    $user = User::factory()->create([
        'avatar' => $fakePath,
    ]);
    $user->assignRole('Member');

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'remove_avatar' => 1,
    ]);

    $response->assertRedirect(route('profile.show'));
    $user->refresh();
    expect($user->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($fakePath);
});
