<?php

use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\TrainerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Trainer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('unauthenticated user is redirected to login when accessing trainers', function () {
    $this->get(route('trainers.index'))
        ->assertRedirect(route('login'));
});

test('member cannot access trainers management', function () {
    $member = User::factory()->create();
    $member->assignRole('Member');

    $this->actingAs($member)
        ->get(route('trainers.index'))
        ->assertForbidden();
});

test('admin can view trainers index page with data', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create(['name' => 'Coach Vicky']);
    $user->assignRole('Trainer');
    $trainer = Trainer::factory()->create([
        'user_id' => $user->id,
        'specialization' => 'Fitness & Bodybuilding',
    ]);

    $this->actingAs($admin)
        ->get(route('trainers.index'))
        ->assertOk()
        ->assertSee('Manajemen /')
        ->assertSee('Trainer')
        ->assertSee('Coach Vicky')
        ->assertSee('Fitness & Bodybuilding')
        ->assertSee('Tambah Trainer');
});

test('kasir cannot access trainers index page and receives 403', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($kasir)
        ->get(route('trainers.index'))
        ->assertForbidden();
});

test('admin can create a new trainer and auto-create user with Trainer role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)
        ->post(route('trainers.store'), [
            'name' => 'Coach Daniel',
            'email' => 'daniel@ifgs.test',
            'password' => 'password123',
            'phone' => '081234567890',
            'specialization' => 'Strength & Conditioning',
            'bio' => 'Pelatih bersertifikat.',
            'status' => 'active',
        ]);

    $response->assertRedirect(route('trainers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Coach Daniel',
        'email' => 'daniel@ifgs.test',
    ]);

    $user = User::where('email', 'daniel@ifgs.test')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('Trainer'))->toBeTrue();

    $this->assertDatabaseHas('trainers', [
        'user_id' => $user->id,
        'specialization' => 'Strength & Conditioning',
        'phone' => '081234567890',
        'status' => 'active',
    ]);

    expect($user->trainer->trainer_code)->toMatch('/^TRN-\d{3}$/');
});

test('admin can update trainer and user details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create(['name' => 'Coach Lama', 'email' => 'lama@ifgs.test']);
    $user->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $user->id, 'specialization' => 'Fitness']);

    $response = $this->actingAs($admin)
        ->put(route('trainers.update', $trainer), [
            'name' => 'Coach Baru',
            'email' => 'baru@ifgs.test',
            'phone' => '08987654321',
            'specialization' => 'Aerobic & Zumba',
            'bio' => 'Bio baru.',
            'status' => 'active',
        ]);

    $response->assertRedirect(route('trainers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Coach Baru',
        'email' => 'baru@ifgs.test',
    ]);

    $this->assertDatabaseHas('trainers', [
        'id' => $trainer->id,
        'specialization' => 'Aerobic & Zumba',
        'phone' => '08987654321',
    ]);
});

test('admin can toggle trainer status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $user->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $user->id, 'status' => 'active']);

    $response = $this->actingAs($admin)
        ->patch(route('trainers.toggle-status', $trainer));

    $response->assertRedirect(route('trainers.index'));
    expect($trainer->fresh()->status)->toBe('inactive');
    expect($user->fresh()->status)->toBe(User::STATUS_INACTIVE);
});

test('admin can delete trainer and associated user account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create();
    $user->assignRole('Trainer');
    $trainer = Trainer::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)
        ->delete(route('trainers.destroy', $trainer));

    $response->assertRedirect(route('trainers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('trainers', ['id' => $trainer->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('TrainerSeeder populates official IFGS trainers', function () {
    $this->seed(TrainerSeeder::class);

    expect(Trainer::count())->toBeGreaterThanOrEqual(2);
    expect(User::role('Trainer')->count())->toBeGreaterThanOrEqual(2);

    $mario = User::where('email', 'mario.trainer@ifgs.test')->first();
    expect($mario)->not->toBeNull();
    expect($mario->hasRole('Trainer'))->toBeTrue();
    expect($mario->trainer->specialization)->toBe('Fitness & Bodybuilding');
});
