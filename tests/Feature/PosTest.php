<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authorized Admin/Manager and Kasir can access /pos', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($admin)->get(route('pos.index'))
        ->assertOk()
        ->assertSee('Transaksi /')
        ->assertSee('POS Kasir');

    $this->actingAs($kasir)->get(route('pos.index'))
        ->assertOk()
        ->assertSee('POS Kasir');
});

test('unauthorized Member role receives 403 on /pos', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $this->actingAs($memberUser)->get(route('pos.index'))->assertForbidden();
});

test('unauthenticated guest is redirected to login from /pos', function () {
    $this->get(route('pos.index'))->assertRedirect(route('login'));
});

test('pos transaction creates transaction, transaction item, and membership with accurate relationships', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $memberUser = User::factory()->create(['name' => 'Mario Teguh', 'status' => User::STATUS_ACTIVE]);
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $product = Product::factory()->create([
        'name' => 'Paket Gym 1 Bulan',
        'price' => 150000.00,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
        'status' => Product::STATUS_ACTIVE,
    ]);

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Tunai',
        'code' => 'cash',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $startDate = '2026-04-01';

    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
        'start_date' => $startDate,
        'paid_amount' => 200000.00,
        'notes' => 'Pembayaran tunai di kasir utama',
    ]);

    $response->assertRedirect(route('pos.index'));
    $response->assertSessionHas('success');
    $response->assertSessionHas('last_transaction_id');

    // 1. Verify Transaction created
    $transaction = Transaction::where('member_id', $member->id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->invoice_number)->toStartWith('TRX-');
    expect($transaction->user_id)->toBe($cashier->id);
    expect($transaction->payment_method_id)->toBe($paymentMethod->id);
    expect((float) $transaction->total_amount)->toBe(150000.00);
    expect((float) $transaction->paid_amount)->toBe(200000.00);
    expect((float) $transaction->change_amount)->toBe(50000.00);
    expect($transaction->status)->toBe(Transaction::STATUS_COMPLETED);

    // 2. Verify TransactionItem created
    expect($transaction->items)->toHaveCount(1);
    $item = $transaction->items->first();
    expect($item->product_id)->toBe($product->id);
    expect($item->product_name)->toBe('Paket Gym 1 Bulan');
    expect((float) $item->price)->toBe(150000.00);
    expect($item->quantity)->toBe(1);
    expect((float) $item->subtotal)->toBe(150000.00);

    // 3. Verify Membership created and linked
    $membership = Membership::where('transaction_id', $transaction->id)->first();
    expect($membership)->not->toBeNull();
    expect($membership->member_id)->toBe($member->id);
    expect($membership->product_id)->toBe($product->id);
    expect($membership->payment_method_id)->toBe($paymentMethod->id);
    expect($membership->start_date->format('Y-m-d'))->toBe('2026-04-01');
    expect($membership->end_date->format('Y-m-d'))->toBe('2026-05-01');
    expect((float) $membership->price)->toBe(150000.00);
    expect($membership->status)->toBe(Membership::STATUS_ACTIVE);
    expect($transaction->membership->id)->toBe($membership->id);
});

test('price snapshot ensures transaction and membership prices remain constant when product price changes', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $member = Member::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Paket Gym 3 Bulan',
        'price' => 400000.00,
        'duration_value' => 3,
        'duration_unit' => Product::DURATION_MONTH,
        'status' => Product::STATUS_ACTIVE,
    ]);
    $pm = PaymentMethod::factory()->create(['code' => 'bank_transfer', 'status' => PaymentMethod::STATUS_ACTIVE]);

    $this->actingAs($cashier)->post(route('pos.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $pm->id,
        'start_date' => now()->format('Y-m-d'),
    ]);

    $transaction = Transaction::where('member_id', $member->id)->first();
    $membership = Membership::where('transaction_id', $transaction->id)->first();
    $item = TransactionItem::where('transaction_id', $transaction->id)->first();

    expect((float) $transaction->total_amount)->toBe(400000.00);
    expect((float) $membership->price)->toBe(400000.00);
    expect((float) $item->price)->toBe(400000.00);

    // Change master product price to 550.000
    $product->update(['price' => 550000.00]);

    // Transaction, Item, and Membership must still have 400.000 snapshot
    $transaction->refresh();
    $membership->refresh();
    $item->refresh();

    expect((float) $transaction->total_amount)->toBe(400000.00);
    expect((float) $membership->price)->toBe(400000.00);
    expect((float) $item->price)->toBe(400000.00);
});

test('cash payment rejects insufficient paid amount', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $member = Member::factory()->create();
    $product = Product::factory()->create([
        'price' => 150000.00,
        'status' => Product::STATUS_ACTIVE,
    ]);
    $pm = PaymentMethod::factory()->create(['code' => 'cash', 'status' => PaymentMethod::STATUS_ACTIVE]);

    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $pm->id,
        'start_date' => now()->format('Y-m-d'),
        'paid_amount' => 100000.00, // Less than 150.000
    ]);

    $response->assertSessionHas('error');
    expect(Transaction::count())->toBe(0);
    expect(Membership::count())->toBe(0);
});

test('cannot create transaction with inactive product', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $member = Member::factory()->create();
    $inactiveProduct = Product::factory()->inactive()->create();
    $pm = PaymentMethod::factory()->create(['status' => PaymentMethod::STATUS_ACTIVE]);

    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'member_id' => $member->id,
        'product_id' => $inactiveProduct->id,
        'payment_method_id' => $pm->id,
        'start_date' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHas('error');
    expect(Transaction::count())->toBe(0);
});

test('cannot create transaction with inactive payment method', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $member = Member::factory()->create();
    $product = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
    $inactivePm = PaymentMethod::factory()->inactive()->create();

    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $inactivePm->id,
        'start_date' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors(['payment_method_id']);
    expect(Transaction::count())->toBe(0);
});

test('pos calculate endpoint returns calculated end date and pricing', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $product = Product::factory()->create([
        'price' => 200000.00,
        'duration_value' => 2,
        'duration_unit' => Product::DURATION_MONTH,
    ]);

    $response = $this->actingAs($cashier)->getJson(route('pos.calculate', [
        'product_id' => $product->id,
        'start_date' => '2026-05-01',
    ]));

    $response->assertOk()
        ->assertJson([
            'end_date' => '2026-07-01',
            'price' => 200000.00,
            'duration_formatted' => '2 Bulan',
        ]);
});

test('pos receipt view renders transaction details', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('Kasir');

    $transaction = Transaction::factory()->create([
        'total_amount' => 150000.00,
        'paid_amount' => 150000.00,
        'change_amount' => 0.00,
    ]);

    $response = $this->actingAs($cashier)->get(route('pos.receipt', $transaction));

    $response->assertOk()
        ->assertSee($transaction->invoice_number)
        ->assertSee('INDO FITNESS GYM SPORT');
});
