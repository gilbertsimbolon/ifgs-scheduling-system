<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed standard roles
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('pengguna index page can be rendered and contains all four modals and triggers', function () {
    $user = User::factory()->create([
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Admin/Manager');

    $response = $this->actingAs($user)->get(route('pengguna.index'));

    $response->assertStatus(200);
    $response->assertSee('Gilbert Simbolon');
    $response->assertSee('gilbert-simbolon');
    $response->assertSee('gilbert@ifgs.test');
    $response->assertSee('Admin/Manager');
    $response->assertSee('Aktif');

    // Verify all 4 modals exist in the DOM
    $response->assertSee('id="modalTambahPengguna"', false);
    $response->assertSee('id="modalDetailPengguna"', false);
    $response->assertSee('id="modalEditPengguna"', false);
    $response->assertSee('id="modalHapusPengguna"', false);

    // Verify action triggers for modals
    $response->assertSee('data-bs-target="#modalTambahPengguna"', false);
    $response->assertSee('data-bs-target="#modalDetailPengguna"', false);
    $response->assertSee('data-bs-target="#modalEditPengguna"', false);
    $response->assertSee('data-bs-target="#modalHapusPengguna"', false);
});

test('no separate standalone pages exist for create, show, and edit', function () {
    expect(Route::has('pengguna.create'))->toBeFalse();
    expect(Route::has('pengguna.show'))->toBeFalse();
    expect(Route::has('pengguna.edit'))->toBeFalse();

    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    // GET requests to non-existent create, show, and edit routes do not return 200
    $this->actingAs($admin)->get('/pengguna/tambah')->assertStatus(405);
    $this->actingAs($admin)->get('/pengguna/gilbert-simbolon')->assertStatus(405);
    $this->actingAs($admin)->get('/pengguna/gilbert-simbolon/edit')->assertStatus(404);
});

test('unauthenticated guest cannot access /pengguna and is redirected to login', function () {
    $response = $this->get(route('pengguna.index'));
    $response->assertRedirect(route('login'));
});

test('kasir role can view pengguna index (read-only)', function () {
    $kasirUser = User::factory()->create();
    $kasirUser->assignRole('Kasir');

    $response = $this->actingAs($kasirUser)->get(route('pengguna.index'));
    $response->assertStatus(200);
});

test('member role cannot access /pengguna and receives 403', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $response = $this->actingAs($memberUser)->get(route('pengguna.index'));
    $response->assertStatus(403);
});

test('pengguna index can filter by search term', function () {
    $admin = User::factory()->create(['name' => 'Admin User', 'email' => 'adminuser@ifgs.test']);
    $admin->assignRole('Admin/Manager');

    $user1 = User::factory()->create(['name' => 'Gilbert Simbolon', 'email' => 'gilbert@ifgs.test']);
    $user1->assignRole('Admin/Manager');

    $user2 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@ifgs.test']);
    $user2->assignRole('Kasir');

    $response = $this->actingAs($admin)->get(route('pengguna.index', ['search' => 'Gilbert']));
    $response->assertStatus(200);
    $response->assertSee('Gilbert Simbolon');
    $response->assertDontSee('John Doe');
});

test('pengguna index can filter by role', function () {
    $admin = User::factory()->create(['name' => 'Gilbert Admin']);
    $admin->assignRole('Admin/Manager');
    $loggedInAdmin = User::factory()->create(['name' => 'Current Admin']);
    $loggedInAdmin->assignRole('Admin/Manager');

    $adminOther = User::factory()->create(['name' => 'Gilbert Admin']);
    $adminOther->assignRole('Admin/Manager');

    $user2 = User::factory()->create(['name' => 'John Kasir']);
    $user2->assignRole('Kasir');

    $response = $this->actingAs($admin)->get(route('pengguna.index', ['role' => 'Kasir']));
    $response = $this->actingAs($loggedInAdmin)->get(route('pengguna.index', ['role' => 'Kasir']));
    $response->assertStatus(200);
    $response->assertSee('John Kasir');
    $response->assertDontSee('Gilbert Admin');
});

test('pengguna index can filter by status', function () {
    $admin = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $admin->assignRole('Admin/Manager');

    User::factory()->create(['name' => 'Active User', 'status' => User::STATUS_ACTIVE]);
    User::factory()->create(['name' => 'Inactive User', 'status' => User::STATUS_INACTIVE]);

    $response = $this->actingAs($admin)->get(route('pengguna.index', ['status' => User::STATUS_INACTIVE]));
    $response->assertStatus(200);
    $response->assertSee('Inactive User');
    $response->assertDontSee('Active User');
});

test('pengguna store validates input and creates a user with slug and Spatie role, staying on /pengguna', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $data = [
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
        'password' => 'password123',
        'role' => 'Admin/Manager',
        'status' => User::STATUS_ACTIVE,
    ];

    $response = $this->actingAs($admin)->post(route('pengguna.store'), $data);

    $response->assertRedirect(route('pengguna.index'));
    $response->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
        'slug' => 'gilbert-simbolon',
    ]);

    $user = User::where('email', 'gilbert@ifgs.test')->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($user->hasRole('Admin/Manager'))->toBeTrue();
});

test('pengguna slug is generated uniquely when names collide', function () {
    $user1 = User::create([
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert1@ifgs.test',
        'password' => 'password123',
        'status' => User::STATUS_ACTIVE,
    ]);

    $user2 = User::create([
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert2@ifgs.test',
        'password' => 'password123',
        'status' => User::STATUS_ACTIVE,
    ]);

    expect($user1->slug)->toBe('gilbert-simbolon');
    expect($user2->slug)->toBe('gilbert-simbolon-2');
});

test('pengguna update updates user data, slug, and syncs role, staying on /pengguna', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Kasir');

    $response = $this->actingAs($admin)->put(route('pengguna.update', $user->slug), [
        'name' => 'New Name',
        'email' => 'new@ifgs.test',
        'password' => 'newpassword123',
        'role' => 'Admin/Manager',
        'status' => User::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('pengguna.index'));
    $response->assertSessionHas('success', 'Pengguna berhasil diperbarui.');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->slug)->toBe('new-name');
    expect($user->email)->toBe('new@ifgs.test');
    expect($user->status)->toBe(User::STATUS_INACTIVE);
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
    expect($user->hasRole('Admin/Manager'))->toBeTrue();
    expect($user->hasRole('Kasir'))->toBeFalse();
});

test('pengguna update keeps existing password when password field is empty', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@ifgs.test',
        'password' => 'originalpassword',
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Kasir');

    $oldHashedPassword = $user->password;

    $response = $this->actingAs($admin)->put(route('pengguna.update', $user->slug), [
        'name' => 'Test User Updated',
        'email' => 'test@ifgs.test',
        'password' => '',
        'role' => 'Kasir',
        'status' => User::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('pengguna.index'));
    $user->refresh();
    expect($user->password)->toBe($oldHashedPassword);
});

test('pengguna destroy deletes the user and redirects to /pengguna', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'User To Delete',
        'email' => 'delete@ifgs.test',
    ]);

    $response = $this->actingAs($admin)->delete(route('pengguna.destroy', $user->slug));

    $response->assertRedirect(route('pengguna.index'));
    $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('pengguna toggle status switches active to inactive and returns json', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Active User',
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($admin)->patchJson(route('pengguna.toggle-status', $user->slug));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status' => User::STATUS_INACTIVE,
        'label' => 'Tidak Aktif',
    ]);

    $user->refresh();
    expect($user->status)->toBe(User::STATUS_INACTIVE);
});

test('pengguna toggle status switches inactive to active and returns json', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Inactive User',
        'status' => User::STATUS_INACTIVE,
    ]);

    $response = $this->actingAs($admin)->patchJson(route('pengguna.toggle-status', $user->slug));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status' => User::STATUS_ACTIVE,
        'label' => 'Aktif',
    ]);

    $user->refresh();
    expect($user->status)->toBe(User::STATUS_ACTIVE);
});

test('pengguna index includes all users and can filter by role Member', function () {
    $loggedInAdmin = User::factory()->create(['name' => 'Current Admin']);
    $loggedInAdmin->assignRole('Admin/Manager');

    $admin = User::factory()->create(['name' => 'Admin Staff']);
    $admin->assignRole('Admin/Manager');

    $kasir = User::factory()->create(['name' => 'Kasir Staff']);
    $kasir->assignRole('Kasir');

    $memberUser = User::factory()->create(['name' => 'Gym Customer']);
    $memberUser->assignRole('Member');

    $response = $this->actingAs($admin)->get(route('pengguna.index'));
    $response = $this->actingAs($loggedInAdmin)->get(route('pengguna.index'));

    $response->assertStatus(200);
    $response->assertSee('Admin Staff');
    $response->assertSee('Kasir Staff');
    $response->assertSee('Gym Customer');
    $response->assertViewHas('roles', function ($roles) {
        return $roles->contains('Member') && $roles->contains('Admin/Manager') && $roles->contains('Kasir');
    });

    // Filter by role Member
    $filterResponse = $this->actingAs($admin)->get(route('pengguna.index', ['role' => 'Member']));
    $filterResponse = $this->actingAs($loggedInAdmin)->get(route('pengguna.index', ['role' => 'Member']));
    $filterResponse->assertStatus(200);
    $filterResponse->assertSee('Gym Customer');
    $filterResponse->assertDontSee('Admin Staff');
});

test('can assign Member role via pengguna store and auto-creates Member profile', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('pengguna.store'), [
        'name' => 'Member User',
        'email' => 'memberuser@ifgs.test',
        'password' => 'password123',
        'role' => 'Member',
        'phone' => '08123456789',
    ]);

    $response->assertRedirect(route('pengguna.index'));
    $this->assertDatabaseHas('users', ['email' => 'memberuser@ifgs.test']);

    // Member profile auto-created
    $user = User::where('email', 'memberuser@ifgs.test')->first();
    $this->assertDatabaseHas('members', [
        'user_id' => $user->id,
        'phone' => '08123456789',
    ]);
    $this->assertNotNull($user->member);
    $this->assertStringStartsWith('IFGS-', $user->member->member_code);
});
