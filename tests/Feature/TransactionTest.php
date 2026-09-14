<?php

use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authorized Admin/Manager and Kasir can access /transactions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($admin)->get(route('transactions.index'))
        ->assertOk()
        ->assertSee('Transaksi /')
        ->assertSee('Riwayat Transaksi');

    $this->actingAs($kasir)->get(route('transactions.index'))
        ->assertOk()
        ->assertSee('Riwayat Transaksi');
});

test('unauthorized Member role receives 403 on /transactions', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $this->actingAs($memberUser)->get(route('transactions.index'))->assertForbidden();
});

test('can filter transactions by search keyword', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $targetMemberUser = User::factory()->create(['name' => 'Zulhasan Syarif']);
    $targetMember = Member::factory()->create(['user_id' => $targetMemberUser->id]);
    $targetTrx = Transaction::factory()->create([
        'member_id' => $targetMember->id,
        'invoice_number' => 'TRX-20260910-9999',
    ]);

    $otherTrx = Transaction::factory()->create();

    $response = $this->actingAs($admin)->get(route('transactions.index', ['search' => 'Zulhasan']));

    $response->assertOk()
        ->assertSee('TRX-20260910-9999')
        ->assertSee('Zulhasan Syarif');
});

test('can view transaction show details via JSON ajax request', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $trx = Transaction::factory()->create([
        'total_amount' => 150000.00,
        'paid_amount' => 150000.00,
        'change_amount' => 0.00,
    ]);

    $response = $this->actingAs($admin)->getJson(route('transactions.show', $trx));

    $response->assertOk()
        ->assertJson([
            'id' => $trx->id,
            'invoice_number' => $trx->invoice_number,
        ]);
});

test('can render transaction receipt print view', function () {
    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $trx = Transaction::factory()->create();

    $response = $this->actingAs($kasir)->get(route('transactions.receipt', $trx));

    $response->assertOk()
        ->assertSee('INDO FITNESS GYM SPORT')
        ->assertSee($trx->invoice_number);
});
