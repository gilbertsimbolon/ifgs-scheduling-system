<?php

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

test('tamu tidak dapat mengakses halaman kasir dan diarahkan ke login', function () {
    $response = $this->get(route('kasir.index'));
    $response->assertRedirect(route('login'));
});

test('peran member tidak dapat mengakses halaman kasir dan mendapat 403', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $response = $this->actingAs($memberUser)->get(route('kasir.index'));
    $response->assertStatus(403);
});

test('peran kasir dapat mengakses halaman kasir dan melihat kartu-kartu aksi', function () {
    $kasir = User::factory()->create(['name' => 'Kasir IFGS']);
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($kasir)->get(route('kasir.index'));

    $response->assertOk();
    $response->assertSee('Mode Kasir / Front Desk');
    $response->assertSee('Selamat Datang, Kasir IFGS!');
    $response->assertSee('Tambah Pengguna');
    $response->assertSee('Transaksi Membership');
    $response->assertSee('Presensi & Cek-in', false);
    $response->assertSee('modalTambahPengguna');
});

test('peran admin/manager dapat mengakses halaman kasir', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('kasir.index'));

    $response->assertOk();
    $response->assertSee('Mode Kasir / Front Desk');
});

test('login dengan peran kasir langsung diarahkan ke halaman kasir', function () {
    $kasir = User::factory()->create([
        'email' => 'kasir@ifgs.com',
        'password' => bcrypt('password123'),
        'status' => User::STATUS_ACTIVE,
    ]);
    $kasir->assignRole('Kasir');

    $response = $this->post(route('login.submit'), [
        'email' => 'kasir@ifgs.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('kasir.index'));
    $this->assertAuthenticatedAs($kasir);
});

test('kasir yang mencoba membuka /dashboard diarahkan langsung ke halaman kasir', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($kasir)->get(route('dashboard'));

    $response->assertRedirect(route('kasir.index'));
});

test('kasir dapat mendaftarkan pengguna baru dan diarahkan kembali ke halaman kasir', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $payload = [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'role' => 'Member',
        'phone' => '08123456789',
    ];

    $response = $this->actingAs($kasir)->post(route('pengguna.store'), $payload);

    $response->assertRedirect(route('kasir.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
    ]);

    $newUser = User::where('email', 'budi@example.com')->first();
    expect($newUser->hasRole('Member'))->toBeTrue();
    expect($newUser->member)->not->toBeNull();
    expect($newUser->member->phone)->toBe('08123456789');
});

test('sidebar menampilkan menu tunggal kasir saat login sebagai kasir', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($kasir)->get(route('kasir.index'));

    $response->assertOk();
    $response->assertSee('Kasir / Front Desk');
    $response->assertDontSee('Jadwal Operasional');
});
