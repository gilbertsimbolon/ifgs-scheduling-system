<?php

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

    $response = $this->get(route('pengguna.index'));

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

    // GET requests to non-existent create, show, and edit routes do not return 200
    $this->get('/pengguna/tambah')->assertStatus(405);
    $this->get('/pengguna/gilbert-simbolon')->assertStatus(405);
    $this->get('/pengguna/gilbert-simbolon/edit')->assertStatus(404);
});

test('pengguna index can filter by search term', function () {
    $user1 = User::factory()->create(['name' => 'Gilbert Simbolon', 'email' => 'gilbert@ifgs.test']);
    $user1->assignRole('Admin/Manager');

    $user2 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@ifgs.test']);
    $user2->assignRole('Kasir');

    $response = $this->get(route('pengguna.index', ['search' => 'Gilbert']));
    $response->assertStatus(200);
    $response->assertSee('Gilbert Simbolon');
    $response->assertDontSee('John Doe');
});

test('pengguna index can filter by role', function () {
    $user1 = User::factory()->create(['name' => 'Gilbert Admin']);
    $user1->assignRole('Admin/Manager');

    $user2 = User::factory()->create(['name' => 'John Kasir']);
    $user2->assignRole('Kasir');

    $response = $this->get(route('pengguna.index', ['role' => 'Kasir']));
    $response->assertStatus(200);
    $response->assertSee('John Kasir');
    $response->assertDontSee('Gilbert Admin');
});

test('pengguna index can filter by status', function () {
    User::factory()->create(['name' => 'Active User', 'status' => User::STATUS_ACTIVE]);
    User::factory()->create(['name' => 'Inactive User', 'status' => User::STATUS_INACTIVE]);

    $response = $this->get(route('pengguna.index', ['status' => User::STATUS_INACTIVE]));
    $response->assertStatus(200);
    $response->assertSee('Inactive User');
    $response->assertDontSee('Active User');
});

test('pengguna store validates input and creates a user with slug and Spatie role, staying on /pengguna', function () {
    $data = [
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
        'password' => 'password123',
        'role' => 'Admin/Manager',
        'status' => User::STATUS_ACTIVE,
    ];

    $response = $this->post(route('pengguna.store'), $data);

    $response->assertRedirect(route('pengguna.index'));
    $response->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
        'slug' => 'gilbert-simbolon',
        'status' => User::STATUS_ACTIVE,
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
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Kasir');

    $response = $this->put(route('pengguna.update', $user->slug), [
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
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@ifgs.test',
        'password' => 'originalpassword',
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Kasir');

    $oldHashedPassword = $user->password;

    $response = $this->put(route('pengguna.update', $user->slug), [
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
    $user = User::factory()->create([
        'name' => 'User To Delete',
        'email' => 'delete@ifgs.test',
    ]);

    $response = $this->delete(route('pengguna.destroy', $user->slug));

    $response->assertRedirect(route('pengguna.index'));
    $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
