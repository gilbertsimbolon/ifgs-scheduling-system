<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Trainer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('unauthenticated user is redirected to login when accessing checkin-checkout page', function () {
    $this->get(route('attendances.index'))
        ->assertRedirect(route('login'));
});

test('member role cannot access checkin-checkout operational scanner page', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $this->actingAs($memberUser)
        ->get(route('attendances.index'))
        ->assertForbidden();
});

test('admin can access checkin-checkout page and view scanner and branding', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $this->actingAs($admin)
        ->get(route('attendances.index'))
        ->assertOk()
        ->assertSee('Indo Fitness Gym Sport')
        ->assertSee('Silahkan scan barcode Anda terlebih dahulu untuk check in/checkout.')
        ->assertSee('KOMUNITAS PALING SPAN!!!')
        ->assertSee('id="cameraWrapper"', false)
        ->assertSee('id="reader"', false);
});

test('kasir and trainer can access checkin-checkout page', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $trainerUser = User::factory()->create();
    $trainerUser->assignRole('Trainer');

    $this->actingAs($kasir)
        ->get(route('attendances.index'))
        ->assertOk();

    $this->actingAs($trainerUser)
        ->get(route('attendances.index'))
        ->assertOk();
});

test('scan check-in creates attendance record with status checked_in', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create(['name' => 'Budi Santoso']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create([
        'user_id' => $memberUser->id,
        'member_code' => 'IFGS-M-001',
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => 'IFGS-M-001',
            'mode' => 'auto',
            'method' => 'barcode_scanner',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'check_in',
        ])
        ->assertJsonPath('member.name', 'Budi Santoso');

    $this->assertDatabaseHas('attendances', [
        'member_id' => $member->id,
        'status' => Attendance::STATUS_CHECKED_IN,
        'scan_method' => 'barcode_scanner',
    ]);
});

test('scan check-in can locate member via user qr_code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create([
        'name' => 'Siti Nurhaliza',
        'qr_code' => 'IFGS-QR-ABC12345',
    ]);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => 'IFGS-QR-ABC12345',
            'mode' => 'check_in',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'check_in',
        ]);

    $this->assertDatabaseHas('attendances', [
        'member_id' => $member->id,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);
});

test('scan check-in works for user without prior member record', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $standaloneUser = User::factory()->create([
        'name' => 'Admin Tester',
        'user_code' => 'IFGS-202609-0099',
        'qr_code' => 'IFGS-202609-0099',
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => 'IFGS-202609-0099',
            'mode' => 'check_in',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'check_in',
        ]);

    $this->assertDatabaseHas('members', [
        'user_id' => $standaloneUser->id,
    ]);
});

test('scan check-in automatically activates approved trainer booking for today to in_progress', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $trainerUser = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $memberUser = User::factory()->create(['name' => 'Rian Hidayat']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'session_date' => today()->toDateString(),
        'status' => TrainerBooking::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => $member->member_code,
            'mode' => 'auto',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'check_in',
        ]);

    expect($booking->fresh()->status)->toBe(TrainerBooking::STATUS_IN_PROGRESS);

    $this->assertDatabaseHas('attendances', [
        'member_id' => $member->id,
        'trainer_booking_id' => $booking->id,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);
});

test('scan check-out marks attendance as completed and completes active trainer session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $trainerUser = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $memberUser = User::factory()->create(['name' => 'Rian Hidayat']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'session_date' => today()->toDateString(),
        'status' => TrainerBooking::STATUS_IN_PROGRESS,
    ]);

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'trainer_booking_id' => $booking->id,
        'date' => today()->toDateString(),
        'check_in_at' => now()->subHour(),
        'check_out_at' => null,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => $member->member_code,
            'mode' => 'auto',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'check_out',
        ]);

    expect($attendance->fresh()->status)->toBe(Attendance::STATUS_COMPLETED);
    expect($attendance->fresh()->check_out_at)->not->toBeNull();
    expect($booking->fresh()->status)->toBe(TrainerBooking::STATUS_COMPLETED);
    expect($booking->fresh()->completed_at)->not->toBeNull();
});

test('scan in auto mode within cooldown period returns already_checked_in and does not check out', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create(['name' => 'Doni Silaban']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'date' => today()->toDateString(),
        'check_in_at' => now()->subSeconds(15),
        'check_out_at' => null,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => $member->member_code,
            'mode' => 'auto',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'already_checked_in',
        ]);

    expect($attendance->fresh()->status)->toBe(Attendance::STATUS_CHECKED_IN);
    expect($attendance->fresh()->check_out_at)->toBeNull();
});

test('scan check-in fails if member is already checked in', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    Attendance::factory()->create([
        'member_id' => $member->id,
        'date' => today()->toDateString(),
        'check_in_at' => now()->subMinutes(30),
        'check_out_at' => null,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => $member->member_code,
            'mode' => 'check_in',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('scan check-out fails if member has not checked in today', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => $member->member_code,
            'mode' => 'check_out',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('scan with unknown code returns 404', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)
        ->postJson(route('attendances.scan'), [
            'code' => 'NON-EXISTENT-CODE-999',
            'mode' => 'auto',
        ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
        ]);
});

test('quick checkout button successfully checks out member from active in-gym list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'date' => today()->toDateString(),
        'check_in_at' => now()->subMinutes(45),
        'check_out_at' => null,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);

    $this->actingAs($admin)
        ->patch(route('attendances.checkout', $attendance))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($attendance->fresh()->status)->toBe(Attendance::STATUS_COMPLETED);
    expect($attendance->fresh()->check_out_at)->not->toBeNull();
});

test('admin can delete attendance record and it reverts in-progress booking', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $trainerUser = User::factory()->create();
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'session_date' => today()->toDateString(),
        'status' => TrainerBooking::STATUS_IN_PROGRESS,
    ]);

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'trainer_booking_id' => $booking->id,
        'date' => today()->toDateString(),
        'check_in_at' => now()->subMinutes(10),
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);

    $this->actingAs($admin)
        ->delete(route('attendances.destroy', $attendance))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    expect($booking->fresh()->status)->toBe(TrainerBooking::STATUS_APPROVED);
});

test('kasir cannot delete attendance record', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'status' => Attendance::STATUS_CHECKED_IN,
    ]);

    $this->actingAs($kasir)
        ->delete(route('attendances.destroy', $attendance))
        ->assertForbidden();
});
