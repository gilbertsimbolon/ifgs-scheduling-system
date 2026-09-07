<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authorized user with Admin/Manager role can access /member', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Daftar Member');
    $response->assertSee('Home /');
    $response->assertSee('Kelola data member yang terdaftar pada sistem.');
});

test('authorized user with Kasir role can access /member', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($kasir)->get(route('member.index'));

    $response->assertStatus(200);
});

test('unauthorized user with Member role cannot access /member', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $response = $this->actingAs($memberUser)->get(route('member.index'));

    $response->assertStatus(403);
});

test('unauthenticated guest cannot access /member and is redirected to login', function () {
    $response = $this->get(route('member.index'));

    $response->assertRedirect(route('login'));
});

test('member list displays member_code, user name, email, phone, and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);

    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0001',
        'phone' => '081234567890',
    ]);

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('IFGS-202609-0001');
    $response->assertSee('Budi Santoso');
    $response->assertSee('budi@ifgs.test');
    $response->assertSee('081234567890');
    $response->assertSee('Aktif');
});

test('member with null phone displays dash and does not cause error', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Siti Rahma',
        'email' => 'siti@ifgs.test',
    ]);

    Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0002',
        'phone' => null,
    ]);

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Siti Rahma');
    $response->assertSee('IFGS-202609-0002');
    $response->assertSee('-');
});

test('member list with no data displays clean empty state without error', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Belum ada member');
    $response->assertSee('Belum ada member yang terdaftar pada sistem.');
    $response->assertSee('Tidak ada data member');
});

test('pagination works properly when members exceed per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    // Create 15 members
    Member::factory()->count(15)->create();

    $response = $this->actingAs($admin)->get(route('member.index'));

    $response->assertStatus(200);
    $response->assertSee('Menampilkan 1–10 dari 15 member');

    // Page 2
    $responsePage2 = $this->actingAs($admin)->get(route('member.index', ['page' => 2]));
    $responsePage2->assertStatus(200);
    $responsePage2->assertSee('Menampilkan 11–15 dari 15 member');
});

test('admin can store a new member with user and role Member', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('member.store'), [
        'name' => 'Michael Jordan',
        'email' => 'michael@ifgs.test',
        'phone' => '081299887766',
        'password' => 'password123',
        'status' => User::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('member.index'));
    $response->assertSessionHas('success', 'Member berhasil ditambahkan.');

    $user = User::where('email', 'michael@ifgs.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Michael Jordan')
        ->and($user->status)->toBe(User::STATUS_ACTIVE)
        ->and($user->hasRole('Member'))->toBeTrue();

    $member = Member::where('user_id', $user->id)->first();
    expect($member)->not->toBeNull()
        ->and($member->phone)->toBe('081299887766')
        ->and($member->member_code)->toStartWith('IFGS-');
});

test('store member fails with validation errors when inputs are invalid', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $existingUser = User::factory()->create(['email' => 'existing@ifgs.test']);

    $response = $this->actingAs($admin)->post(route('member.store'), [
        'name' => '',
        'email' => 'existing@ifgs.test',
        'phone' => '0812345678901234567890123', // exceeds 20 chars
        'password' => 'short',
        'status' => 'InvalidStatus',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'password', 'status']);
});

test('admin can update member details and user account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@ifgs.test',
        'status' => User::STATUS_ACTIVE,
    ]);
    $user->assignRole('Member');

    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0010',
        'phone' => '0811111111',
    ]);

    $response = $this->actingAs($admin)->put(route('member.update', $member), [
        'name' => 'New Name',
        'email' => 'new@ifgs.test',
        'phone' => '0822222222',
        'password' => 'newpassword123',
        'status' => User::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('member.index'));
    $response->assertSessionHas('success', 'Data member berhasil diperbarui.');

    $user->refresh();
    $member->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@ifgs.test')
        ->and($user->status)->toBe(User::STATUS_INACTIVE)
        ->and(Hash::check('newpassword123', $user->password))->toBeTrue()
        ->and($member->phone)->toBe('0822222222');
});

test('admin can toggle member status via ajax request', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create([
        'name' => 'Toggle User',
        'status' => User::STATUS_ACTIVE,
    ]);
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0020',
    ]);

    $response = $this->actingAs($admin)->patchJson(route('member.toggle-status', $member));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status' => User::STATUS_INACTIVE,
        'label' => 'Tidak Aktif',
    ]);

    expect($user->fresh()->status)->toBe(User::STATUS_INACTIVE);

    // Toggle back to Active
    $response2 = $this->actingAs($admin)->patchJson(route('member.toggle-status', $member));
    $response2->assertStatus(200);
    $response2->assertJson([
        'success' => true,
        'status' => User::STATUS_ACTIVE,
        'label' => 'Aktif',
    ]);

    expect($user->fresh()->status)->toBe(User::STATUS_ACTIVE);
});

test('admin can delete a member and user is also deleted', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user = User::factory()->create(['name' => 'Delete Me']);
    $user->assignRole('Member');
    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0030',
    ]);

    $response = $this->actingAs($admin)->delete(route('member.destroy', $member));

    $response->assertRedirect(route('member.index'));
    $response->assertSessionHas('success', 'Member Delete Me berhasil dihapus.');

    expect(User::find($user->id))->toBeNull();
    expect(Member::find($member->id))->toBeNull();
});

test('member index can filter by search query for name, email, code, or phone', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $user1 = User::factory()->create(['name' => 'Alpha Tester', 'email' => 'alpha@ifgs.test']);
    $member1 = Member::create(['user_id' => $user1->id, 'member_code' => 'IFGS-202609-0041', 'phone' => '0899999999']);

    $user2 = User::factory()->create(['name' => 'Beta Tester', 'email' => 'beta@ifgs.test']);
    $member2 = Member::create(['user_id' => $user2->id, 'member_code' => 'IFGS-202609-0042', 'phone' => '0877777777']);

    // Search by name
    $resName = $this->actingAs($admin)->get(route('member.index', ['search' => 'Alpha']));
    $resName->assertSee('Alpha Tester');
    $resName->assertDontSee('Beta Tester');

    // Search by code
    $resCode = $this->actingAs($admin)->get(route('member.index', ['search' => '0042']));
    $resCode->assertSee('Beta Tester');
    $resCode->assertDontSee('Alpha Tester');

    // Search by phone
    $resPhone = $this->actingAs($admin)->get(route('member.index', ['search' => '0899999999']));
    $resPhone->assertSee('Alpha Tester');
    $resPhone->assertDontSee('Beta Tester');
});

test('member index can filter by status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $activeUser = User::factory()->create(['name' => 'Active Guy', 'status' => User::STATUS_ACTIVE]);
    Member::create(['user_id' => $activeUser->id, 'member_code' => 'IFGS-202609-0051']);

    $inactiveUser = User::factory()->create(['name' => 'Inactive Guy', 'status' => User::STATUS_INACTIVE]);
    Member::create(['user_id' => $inactiveUser->id, 'member_code' => 'IFGS-202609-0052']);

    $resActive = $this->actingAs($admin)->get(route('member.index', ['status' => User::STATUS_ACTIVE]));
    $resActive->assertSee('Active Guy');
    $resActive->assertDontSee('Inactive Guy');

    $resInactive = $this->actingAs($admin)->get(route('member.index', ['status' => User::STATUS_INACTIVE]));
    $resInactive->assertSee('Inactive Guy');
    $resInactive->assertDontSee('Active Guy');
});
