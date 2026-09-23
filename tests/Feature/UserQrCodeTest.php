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

test('user creation automatically generates unique qr_code matching user_code', function () {
    $user = User::factory()->create([
        'name' => 'Jane Member',
        'email' => 'jane@example.com',
    ]);

    expect($user->qr_code)->not->toBeNull()
        ->and($user->qr_code)->toBe($user->user_code)
        ->and($user->qr_code)->toStartWith('IFGS-');
});

test('findByQrCode finds user by qr_code string or member_code', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-998877',
        'phone' => '08123456789',
    ]);

    // Find by QR code string
    $foundByQr = User::findByQrCode($user->qr_code);
    expect($foundByQr)->not->toBeNull()
        ->and($foundByQr->id)->toBe($user->id);

    // Find by member code string
    $foundByMemberCode = User::findByQrCode('MBR-998877');
    expect($foundByMemberCode)->not->toBeNull()
        ->and($foundByMemberCode->id)->toBe($user->id);

    // Non-existent code returns null
    expect(User::findByQrCode('NON-EXISTENT-CODE'))->toBeNull();
});

test('member model delegates qr_code and generates svg helper properly', function () {
    $user = User::factory()->create();
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-001122',
        'phone' => '08123456789',
    ]);

    expect($member->qr_code)->toBe($user->qr_code);

    $svg = $member->getQrCodeSvg(150);
    expect($svg)->toContain('<svg')
        ->and($svg)->toContain('</svg>');
});

test('user registration via register endpoint generates qr_code automatically', function () {
    $response = $this->post(route('register'), [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('login'));

    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->qr_code)->not->toBeNull()
        ->and($user->qr_code)->toStartWith('IFGS-');
});

test('authenticated user can download their own QR code as svg file', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');

    $response = $this->actingAs($user)->get(route('user.qr-code.download'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/svg+xml');
    $response->assertHeader('Content-Disposition', 'attachment; filename="qrcode-'.$user->slug.'.svg"');
    expect($response->getContent())->toContain('<svg');
});

test('staff (Admin/Manager & Kasir) can download any user QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $targetUser = User::factory()->create();
    $targetUser->assignRole('Member');

    $response = $this->actingAs($admin)->get(route('user.qr-code.download.user', $targetUser));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/svg+xml');
    $response->assertHeader('Content-Disposition', 'attachment; filename="qrcode-'.$targetUser->slug.'.svg"');
});

test('member cannot download another user QR code and receives 403', function () {
    $member1 = User::factory()->create();
    $member1->assignRole('Member');

    $member2 = User::factory()->create();
    $member2->assignRole('Member');

    $response = $this->actingAs($member1)->get(route('user.qr-code.download.user', $member2));

    $response->assertStatus(403);
});

test('user can access raw svg stream for their own QR code', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');

    $response = $this->actingAs($user)->get(route('user.qr-code.svg', $user));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/svg+xml');
    expect($response->getContent())->toContain('<svg');
});

test('user can view their own digital member card', function () {
    $user = User::factory()->create(['name' => 'Siti Nurhaliza']);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-123456',
        'phone' => '081298765432',
    ]);

    $response = $this->actingAs($user)->get(route('user.card', $user));

    $response->assertStatus(200);
    $response->assertSee('Kartu Member');
    $response->assertSee('Siti Nurhaliza');
    $response->assertSee('MBR-123456');
    $response->assertSee($user->qr_code);
});

test('staff can view any member digital card', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $memberUser = User::factory()->create(['name' => 'Rian Pratama']);
    $memberUser->assignRole('Member');
    Member::create([
        'user_id' => $memberUser->id,
        'member_code' => 'MBR-654321',
        'phone' => '081211112222',
    ]);

    $response = $this->actingAs($kasir)->get(route('user.card', $memberUser));

    $response->assertStatus(200);
    $response->assertSee('Rian Pratama');
    $response->assertSee('MBR-654321');
});

test('member cannot view another member digital card and receives 403', function () {
    $member1 = User::factory()->create();
    $member1->assignRole('Member');

    $member2 = User::factory()->create();
    $member2->assignRole('Member');

    $response = $this->actingAs($member1)->get(route('user.card', $member2));

    $response->assertStatus(403);
});

test('member landing page displays digital member card and qr code modal', function () {
    $user = User::factory()->create(['name' => 'Member Aktif']);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-778899',
        'phone' => '081233334444',
    ]);

    // Member visiting dashboard is redirected to member portal
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('member.index'));

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('Kartu Member Digital');
    $response->assertSee('QR Absensi Kunjungan');
    $response->assertSee('MBR-778899');
    $response->assertSee($user->qr_code);
    $response->assertSee('modalQrCodeMember');
});

test('member list table displays QR code action button for staff', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create(['name' => 'Member Test']);
    $user->assignRole('Member');
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'MBR-554433',
        'phone' => '081288889999',
    ]);

    $response = $this->actingAs($admin)->get(route('admin-members.index'));

    $response->assertStatus(200);
    $response->assertSee('modalQrCodeAdmin');
    $response->assertSee('bx-qr-scan');
    $response->assertSee($user->qr_code);
});
