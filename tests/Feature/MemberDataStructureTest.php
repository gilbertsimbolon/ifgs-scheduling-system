<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('member can be created from user', function () {
    $user = User::factory()->create([
        'name' => 'Gilbert Simbolon',
        'email' => 'gilbert@ifgs.test',
    ]);

    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0001',
    ]);

    expect($member)->toBeInstanceOf(Member::class)
        ->and($member->user_id)->toBe($user->id)
        ->and($member->member_code)->toBe('IFGS-202609-0001');

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'user_id' => $user->id,
        'member_code' => 'IFGS-202609-0001',
    ]);
});

test('member automatically generates member_code if not provided', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
    ]);

    expect($member->member_code)->not->toBeEmpty()
        ->and($member->member_code)->toStartWith('IFGS-'.now()->format('Ym').'-');

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'user_id' => $user->id,
        'member_code' => $member->member_code,
    ]);
});

test('member_code is unique across multiple members', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $member1 = Member::create(['user_id' => $user1->id]);
    $member2 = Member::create(['user_id' => $user2->id]);

    expect($member1->member_code)->not->toBe($member2->member_code);

    // Attempting to manually create a duplicate member_code throws QueryException
    expect(fn () => Member::create([
        'user_id' => User::factory()->create()->id,
        'member_code' => $member1->member_code,
    ]))->toThrow(QueryException::class);
});

test('user has one member relationship', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $user->id]);

    expect($user->member)->toBeInstanceOf(Member::class)
        ->and($user->member->id)->toBe($member->id)
        ->and($user->member->user_id)->toBe($user->id);
});

test('member belongs to user relationship', function () {
    $user = User::factory()->create([
        'name' => 'Member User',
        'email' => 'member@ifgs.test',
    ]);
    $member = Member::factory()->create(['user_id' => $user->id]);

    expect($member->user)->toBeInstanceOf(User::class)
        ->and($member->user->id)->toBe($user->id)
        ->and($member->user->name)->toBe('Member User')
        ->and($member->user->email)->toBe('member@ifgs.test');
});

test('foreign key user_id is valid and points to users table', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $user->id]);

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'user_id' => $user->id,
    ]);
});

test('member cannot be created with non-existent user_id', function () {
    expect(fn () => Member::create([
        'user_id' => 999999,
        'member_code' => 'IFGS-202609-9999',
    ]))->toThrow(QueryException::class);
});

test('one user cannot have multiple member records', function () {
    $user = User::factory()->create();
    Member::factory()->create(['user_id' => $user->id]);

    expect(fn () => Member::factory()->create(['user_id' => $user->id]))
        ->toThrow(QueryException::class);
});

test('user with role Member can have member record', function () {
    $user = User::factory()->create();
    $user->assignRole('Member');

    $member = Member::factory()->create(['user_id' => $user->id]);

    expect($user->hasRole('Member'))->toBeTrue()
        ->and($member->user->hasRole('Member'))->toBeTrue();
});

test('member record is deleted when user is deleted according to cascade on delete', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $user->id]);

    $this->assertDatabaseHas('members', ['id' => $member->id]);

    $user->delete();

    $this->assertDatabaseMissing('members', ['id' => $member->id]);
    expect(Member::find($member->id))->toBeNull();
});
