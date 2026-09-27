<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\Product;
use App\Models\TimeSlot;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('unauthenticated guest cannot access member portal and is redirected to login', function () {
    $response = $this->get(route('member.index'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('member.reservasi'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('member.riwayat'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('member.paket-layanan'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('member.profil'));
    $response->assertRedirect(route('login'));
});

test('login as member redirects directly to /member', function () {
    $user = User::factory()->create([
        'email' => 'member@ifgs.test',
        'password' => bcrypt('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-MEMBER-01',
        'phone' => '08123456789',
    ]);

    $response = $this->post(route('login.submit'), [
        'email' => 'member@ifgs.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('member.index'));
});

test('login as admin redirects to /dashboard', function () {
    $admin = User::factory()->create([
        'email' => 'admin@ifgs.test',
        'password' => bcrypt('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole('Admin/Manager');

    $response = $this->post(route('login.submit'), [
        'email' => 'admin@ifgs.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
});

test('login as kasir redirects to /dashboard', function () {
    $kasir = User::factory()->create([
        'email' => 'kasir@ifgs.test',
        'password' => bcrypt('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $kasir->assignRole('Kasir');

    $response = $this->post(route('login.submit'), [
        'email' => 'kasir@ifgs.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
});

test('member can access member home (/member) and see personal name and bottom navigation', function () {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0001',
        'phone' => '081234567890',
    ]);

    $response = $this->actingAs($user)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Budi Santoso');
    $response->assertSee('IFGS-2026-0001');
    $response->assertSee('MEMBER CARD');
    $response->assertSee('Reservasi');
    $response->assertSee('Riwayat');
    $response->assertSee('Beranda');
    $response->assertSee('Paket');
    $response->assertSee('Profil');
});

test('member accessing /dashboard is redirected to /member', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0002',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('member.index'));
});

test('admin accessing member portal is redirected to /dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertRedirect(route('dashboard'));
});

test('member can access /member/reservasi and view reservation interface', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0003',
    ]);

    $response = $this->actingAs($user)->get(route('member.reservasi'));

    $response->assertStatus(200);
    $response->assertSee('Reservasi Kunjungan');
    $response->assertSee('Ketentuan Reservasi');
});

test('member can access /member/riwayat and view history tabs', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0004',
    ]);

    $response = $this->actingAs($user)->get(route('member.riwayat'));

    $response->assertStatus(200);
    $response->assertSee('Riwayat Kunjungan');
    $response->assertSee('Kehadiran Gym');
    $response->assertSee('Jadwal Selesai');
});

test('member can access /member/paket-layanan and view products from database', function () {
    Product::factory()->create([
        'name' => 'Membership Bulanan VIP',
        'price' => 250000,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
        'status' => Product::STATUS_ACTIVE,
    ]);

    $user = User::factory()->create();
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0005',
    ]);

    $response = $this->actingAs($user)->get(route('member.paket-layanan'));

    $response->assertStatus(200);
    $response->assertSee('Paket Layanan Gym');
    $response->assertSee('Membership Bulanan VIP');
    $response->assertSee('Rp 250.000');
});

test('member can access /member/profil and view digital qr code and account info', function () {
    $user = User::factory()->create([
        'name' => 'Siti Rahma',
        'email' => 'siti@ifgs.test',
        'user_code' => 'IFGS-USR-0099',
    ]);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0006',
        'phone' => '08987654321',
    ]);

    expect($user->initials)->toBe('SR');

    $response = $this->actingAs($user)->get(route('member.profil'));

    $response->assertStatus(200);
    $response->assertSee('Indo Fitness Gym Sport®');
    $response->assertDontSee('Member Portal');
    $response->assertSee('SR');
    $response->assertSee('Profil Saya');
    $response->assertSee('Siti Rahma');
    $response->assertSee('siti@ifgs.test');
    $response->assertSee('QR Code Presensi Gym');
    $response->assertSee(route('logout'));
});

test('member can update profile via portal and is redirected back to member profil', function () {
    $user = User::factory()->create([
        'name' => 'Budi Awal',
        'email' => 'budi.awal@ifgs.test',
    ]);
    $user->assignRole('Member');
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0007',
        'phone' => '0811111111',
    ]);

    $response = $this->actingAs($user)->put(route('member.profil.update'), [
        'name' => 'Budi Baru',
        'email' => 'budi.baru@ifgs.test',
        'phone' => '0822222222',
    ]);

    $response->assertRedirect(route('member.profil'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Budi Baru')
        ->and($user->email)->toBe('budi.baru@ifgs.test');

    $member->refresh();
    expect($member->phone)->toBe('0822222222');
});

test('member can update password via portal and is redirected back to member profil', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0008',
    ]);

    $response = $this->actingAs($user)->put(route('member.profil.password'), [
        'current_password' => 'oldpassword123',
        'password' => 'newsecretpassword456',
        'password_confirmation' => 'newsecretpassword456',
    ]);

    $response->assertRedirect(route('member.profil'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect(Hash::check('newsecretpassword456', $user->password))->toBeTrue();
});

test('member can directly upload avatar and then delete avatar with x button', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'name' => 'Maca Test',
        'email' => 'maca@ifgs.test',
    ]);
    $user->assignRole('Member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-2026-0009',
    ]);

    expect($user->initials)->toBe('MT');

    // 1. Upload Avatar
    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300);

    $uploadResponse = $this->actingAs($user)->put(route('member.profil.avatar'), [
        'avatar' => $file,
    ]);

    $uploadResponse->assertRedirect(route('member.profil'));
    $uploadResponse->assertSessionHas('success', 'Foto profil berhasil diperbarui.');

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);

    // Profil page shows delete button when avatar exists
    $pageResponse = $this->actingAs($user)->get(route('member.profil'));
    $pageResponse->assertStatus(200);
    $pageResponse->assertSee(route('member.profil.avatar.delete'));
    $pageResponse->assertSee('bx-x');

    // 2. Delete Avatar
    $deleteResponse = $this->actingAs($user)->delete(route('member.profil.avatar.delete'));
    $deleteResponse->assertRedirect(route('member.profil'));
    $deleteResponse->assertSessionHas('success', 'Foto profil berhasil dihapus.');

    $user->refresh();
    expect($user->avatar)->toBeNull();

    // Profil page shows initials again
    $pageAfterDelete = $this->actingAs($user)->get(route('member.profil'));
    $pageAfterDelete->assertSee('MT');
});

test('member with active membership can see 7-day selector, dynamic package cards, and trainer options on reservation modal', function () {
    $user = User::factory()->create(['name' => 'John Gym']);
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $product = Product::factory()->create(['name' => 'Paket Fitness 1 Bulan']);
    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'status' => Membership::STATUS_ACTIVE,
        'start_date' => now()->subDay()->format('Y-m-d'),
        'end_date' => now()->addDays(29)->format('Y-m-d'),
    ]);

    $slotFitness = TimeSlot::factory()->create([
        'name' => 'Sesi Fitness Harian',
        'category' => TimeSlot::CATEGORY_FITNESS,
        'status' => 'active',
    ]);

    $slotZumba = TimeSlot::factory()->create([
        'name' => 'Sesi Zumba Spesial',
        'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
        'status' => 'active',
    ]);

    $trainerUser = User::factory()->create(['name' => 'Coach Mario']);
    $trainer = Trainer::create([
        'user_id' => $trainerUser->id,
        'specialization' => 'Fitness & Bodybuilding',
        'status' => 'active',
        'daily_quota' => 5,
    ]);

    $response = $this->actingAs($user)->get(route('member.reservasi'));

    $response->assertStatus(200);
    // Check modal exists
    $response->assertSee('modalBuatReservasi');
    // Check dynamic 10-day operational selector container
    $response->assertSee('operationalDaysGrid');
    $response->assertSee('days-grid-10');
    $response->assertSee('day-pill-btn');
    // Check package cards (Fitness is subscribed, Zumba is not subscribed)
    $response->assertSee('Sesi Fitness Harian');
    $response->assertSee('Berlangganan');
    $response->assertSee('Sesi Zumba Spesial');
    $response->assertSee('Belum Berlangganan');
    // Check trainer options in Step 2
    $response->assertSee('modalBuatReservasiStep2');
    $response->assertSee('btnNextToStep2');
    $response->assertSee('Tanpa Trainer');
    $response->assertSee('Coach Mario');
    $response->assertSee('Fitness & Bodybuilding');
    $response->assertDontSee('trainingFocusWrapper');
    // Check Confirmation in Step 3
    $response->assertSee('modalBuatReservasiStep3');
    $response->assertSee('btnNextToStep3');
    $response->assertSee('btnSubmitReservasi');
});

test('timeslot generates correct operational dates for fitness and zumba cycling', function () {
    $fitness = TimeSlot::factory()->create([
        'category' => TimeSlot::CATEGORY_FITNESS,
        'days' => 'Senin - Sabtu',
    ]);

    $zumba = TimeSlot::factory()->create([
        'category' => TimeSlot::CATEGORY_AEROBIC_ZUMBA,
        'days' => 'Senin & Kamis',
    ]);

    $fitnessDates = $fitness->getUpcomingOperationalDates(10, Carbon::parse('2026-09-21')); // Monday
    expect($fitnessDates)->toHaveCount(10);
    // Ensure none of the fitness dates is Sunday
    foreach ($fitnessDates as $d) {
        expect($d->dayOfWeek)->not->toBe(CarbonInterface::SUNDAY);
    }

    $zumbaDates = $zumba->getUpcomingOperationalDates(10, Carbon::parse('2026-09-21')); // Monday
    expect($zumbaDates)->toHaveCount(10);
    // Ensure all zumba dates are only Monday or Thursday
    foreach ($zumbaDates as $d) {
        expect(in_array($d->dayOfWeek, [CarbonInterface::MONDAY, CarbonInterface::THURSDAY], true))->toBeTrue();
    }
});

test('member can submit reservation with trainer without training_focus', function () {
    $user = User::factory()->create(['name' => 'Member Test']);
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);

    $product = Product::factory()->create(['name' => 'Paket Fitness 1 Bulan']);
    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'status' => Membership::STATUS_ACTIVE,
        'start_date' => now()->subDay()->format('Y-m-d'),
        'end_date' => now()->addDays(29)->format('Y-m-d'),
    ]);

    $slotFitness = TimeSlot::factory()->create([
        'name' => 'Sesi Fitness Pagi',
        'category' => TimeSlot::CATEGORY_FITNESS,
        'status' => 'active',
        'capacity' => 50,
        'reservation_quota' => 30,
    ]);

    $trainerUser = User::factory()->create(['name' => 'Coach Mario']);
    $trainer = Trainer::create([
        'user_id' => $trainerUser->id,
        'specialization' => 'Fitness & Bodybuilding',
        'status' => 'active',
        'daily_quota' => 5,
    ]);

    $targetDate = now()->isSunday() ? now()->addDay()->toDateString() : now()->toDateString();

    $response = $this->actingAs($user)->post(route('reservations.store'), [
        'visit_date' => $targetDate,
        'time_slot_id' => $slotFitness->id,
        'trainer_id' => $trainer->id,
    ]);

    $response->assertSessionHas('success');
    expect(TrainerBooking::where('member_id', $member->id)->where('trainer_id', $trainer->id)->count())->toBe(1);
    $booking = TrainerBooking::where('member_id', $member->id)->where('trainer_id', $trainer->id)->first();
    expect($booking->training_focus)->toBeNull();
});
