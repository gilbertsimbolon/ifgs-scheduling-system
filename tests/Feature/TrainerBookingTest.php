<?php

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

test('unauthenticated user is redirected to login when accessing trainer bookings', function () {
    $this->get(route('trainer-bookings.index'))
        ->assertRedirect(route('login'));
});

test('admin can view all trainer bookings on index page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $trainerUser = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create([
        'user_id' => $trainerUser->id,
        'specialization' => 'Fitness & Bodybuilding',
    ]);

    $memberUser = User::factory()->create(['name' => 'John Gymmer']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'session_date' => '2026-09-20',
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->get(route('trainer-bookings.index'))
        ->assertOk()
        ->assertSee('Sesi Trainer')
        ->assertSee('Coach Vicky')
        ->assertSee('John Gymmer')
        ->assertSee('Belum Disetujui')
        ->assertSee($booking->booking_code)
        ->assertDontSee('Ajukan Sesi Latihan'); // Sesuai instruksi: tidak ada tombol ajukan sesi latihan
});

test('trainer only sees their own assigned bookings on index page', function () {
    $trainerUser1 = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser1->assignRole('Trainer');
    $trainer1 = Trainer::factory()->create(['user_id' => $trainerUser1->id]);

    $trainerUser2 = User::factory()->create(['name' => 'Coach Denny']);
    $trainerUser2->assignRole('Trainer');
    $trainer2 = Trainer::factory()->create(['user_id' => $trainerUser2->id]);

    $memberUser1 = User::factory()->create(['name' => 'Member Satu']);
    $memberUser1->assignRole('Member');
    $member1 = Member::factory()->create(['user_id' => $memberUser1->id]);

    $memberUser2 = User::factory()->create(['name' => 'Member Dua']);
    $memberUser2->assignRole('Member');
    $member2 = Member::factory()->create(['user_id' => $memberUser2->id]);

    TrainerBooking::factory()->create([
        'trainer_id' => $trainer1->id,
        'member_id' => $member1->id,
    ]);

    TrainerBooking::factory()->create([
        'trainer_id' => $trainer2->id,
        'member_id' => $member2->id,
    ]);

    $this->actingAs($trainerUser1)
        ->get(route('trainer-bookings.index'))
        ->assertOk()
        ->assertSee('Member Satu')
        ->assertDontSee('Member Dua');
});

test('assigned trainer can approve (menyetujui) a pending booking request', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $trainerUser->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $response = $this->actingAs($trainerUser)
        ->patch(route('trainer-bookings.approve', $booking));

    $response->assertRedirect(route('trainer-bookings.index'))
        ->assertSessionHas('success');

    $booking->refresh();
    expect($booking->status)->toBe(TrainerBooking::STATUS_APPROVED)
        ->and($booking->approved_at)->not->toBeNull();
});

test('trainer cannot approve booking assigned to another trainer', function () {
    $trainerUser1 = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser1->assignRole('Trainer');
    $trainer1 = Trainer::factory()->create(['user_id' => $trainerUser1->id]);

    $trainerUser2 = User::factory()->create(['name' => 'Coach Denny']);
    $trainerUser2->assignRole('Trainer');
    $trainer2 = Trainer::factory()->create(['user_id' => $trainerUser2->id]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer2->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $this->actingAs($trainerUser1)
        ->patch(route('trainer-bookings.approve', $booking))
        ->assertForbidden();

    $booking->refresh();
    expect($booking->status)->toBe(TrainerBooking::STATUS_PENDING);
});

test('trainer can reject (menolak) a booking request with reason and trigger whatsapp link', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser->assignRole('Trainer');
    $trainer = Trainer::factory()->create([
        'user_id' => $trainerUser->id,
        'phone' => '081234567890',
    ]);

    $memberUser = User::factory()->create(['name' => 'Budi Member']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create([
        'user_id' => $memberUser->id,
        'phone' => '08987654321',
    ]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $response = $this->actingAs($trainerUser)
        ->patch(route('trainer-bookings.reject', $booking), [
            'reason' => 'Ada jadwal kompetisi di luar kota.',
            'open_wa' => '1',
        ]);

    $response->assertRedirect(route('trainer-bookings.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('whatsapp_open_url');

    $booking->refresh();
    expect($booking->status)->toBe(TrainerBooking::STATUS_REJECTED)
        ->and($booking->rejection_reason)->toBe('Ada jadwal kompetisi di luar kota.');

    $rejectMessage = $booking->getWhatsAppRejectionMessage();
    expect($rejectMessage)->toContain('Budi Member')
        ->and($rejectMessage)->toContain('Coach Vicky')
        ->and($rejectMessage)->toContain('BERHALANGAN')
        ->and($rejectMessage)->toContain('Ada jadwal kompetisi di luar kota.');
});

test('trainer booking generates structured WhatsApp willingness message with trainer profile and schedule', function () {
    $trainerUser = User::factory()->create(['name' => 'Coach Vicky']);
    $trainer = Trainer::factory()->create([
        'user_id' => $trainerUser->id,
        'specialization' => 'Fitness & Hypertrophy',
        'phone' => '081234567890',
        'bio' => 'Sertifikasi Personal Trainer Nasional',
    ]);

    $memberUser = User::factory()->create(['name' => 'Budi Santoso']);
    $member = Member::factory()->create([
        'user_id' => $memberUser->id,
        'phone' => '089876543210',
    ]);

    $booking = TrainerBooking::factory()->create([
        'trainer_id' => $trainer->id,
        'member_id' => $member->id,
        'session_date' => '2026-09-20',
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $message = $booking->getWhatsAppConfirmationMessage();

    expect($message)->toContain('Budi Santoso')
        ->and($message)->toContain('Coach Vicky')
        ->and($message)->toContain('BERSEDIA')
        ->and($message)->toContain('Fitness & Hypertrophy')
        ->and($message)->toContain('081234567890')
        ->and($message)->toContain('08:00 - 20:00 WITA');

    $waUrl = $booking->whatsapp_url;
    expect($waUrl)->not->toBeNull()
        ->and($waUrl)->toContain('https://wa.me/6289876543210');
});

test('filter by status works properly on index page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $memberUser1 = User::factory()->create(['name' => 'Member Disetujui']);
    $member1 = Member::factory()->create(['user_id' => $memberUser1->id]);

    $memberUser2 = User::factory()->create(['name' => 'Member Belum Disetujui']);
    $member2 = Member::factory()->create(['user_id' => $memberUser2->id]);

    TrainerBooking::factory()->create([
        'member_id' => $member1->id,
        'status' => TrainerBooking::STATUS_APPROVED,
    ]);

    TrainerBooking::factory()->create([
        'member_id' => $member2->id,
        'status' => TrainerBooking::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->get(route('trainer-bookings.index', ['status' => 'approved']))
        ->assertOk()
        ->assertSee('Member Disetujui')
        ->assertDontSee('Member Belum Disetujui');
});

test('empty record state displays centered message when no bookings exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $this->actingAs($admin)
        ->get(route('trainer-bookings.index'))
        ->assertOk()
        ->assertSee('Belum Ada Permohonan Sesi Latihan')
        ->assertSee('Belum ada data permohonan sesi latihan');
});

test('empty record state displays filter reset option when search yields no result', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $this->actingAs($admin)
        ->get(route('trainer-bookings.index', ['search' => 'kata_kunci_acak_tidak_ada']))
        ->assertOk()
        ->assertSee('Tidak Ada Permohonan Sesi yang Sesuai')
        ->assertSee('Reset Filter');
});

test('trainer with no bookings assigned sees empty record state even if other trainers have bookings', function () {
    $trainerUser1 = User::factory()->create(['name' => 'Coach Vicky']);
    $trainerUser1->assignRole('Trainer');
    $trainer1 = Trainer::factory()->create(['user_id' => $trainerUser1->id]);

    $trainerUser2 = User::factory()->create(['name' => 'Coach Denny']);
    $trainerUser2->assignRole('Trainer');
    $trainer2 = Trainer::factory()->create(['user_id' => $trainerUser2->id]);

    // Booking hanya untuk Trainer 2
    TrainerBooking::factory()->create([
        'trainer_id' => $trainer2->id,
    ]);

    // Trainer 1 login
    $this->actingAs($trainerUser1)
        ->get(route('trainer-bookings.index'))
        ->assertOk()
        ->assertSee('Belum Ada Permohonan Sesi Latihan')
        ->assertDontSee('Coach Denny');
});
