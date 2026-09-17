<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('member can submit membership order with payment proof upload and status is pending', function () {
    Storage::fake('public');

    $user = User::factory()->create(['name' => 'John Doe Member']);
    $user->assignRole('Member');

    $product = Product::factory()->create([
        'name' => 'Fitness 1 Bulan',
        'price' => 150000,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
    ]);

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'bca_transfer',
        'type' => 'bank_transfer',
        'account_number' => '1234567890',
        'account_name' => 'IFGS Gym',
    ]);

    $fakeFile = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800);

    $response = $this->actingAs($user)->post(route('memberships.order'), [
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
        'start_date' => now()->toDateString(),
        'payment_proof' => $fakeFile,
        'notes' => 'Transfer dari rekening BCA an John Doe',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Pastikan member dibuat
    $member = Member::where('user_id', $user->id)->first();
    expect($member)->not->toBeNull();

    // Pastikan transaksi pending tersimpan dengan bukti transfer
    $transaction = Transaction::where('member_id', $member->id)->latest()->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->status)->toBe(Transaction::STATUS_PENDING);
    expect($transaction->payment_proof)->not->toBeNull();
    expect($transaction->total_amount)->toBe('150000.00');

    Storage::disk('public')->assertExists($transaction->payment_proof);

    // Pastikan membership pending tersimpan
    $membership = Membership::where('transaction_id', $transaction->id)->first();
    expect($membership)->not->toBeNull();
    expect($membership->status)->toBe(Membership::STATUS_PENDING);
    expect($membership->price)->toBe('150000.00');
});

test('cashier can view pending transactions in dedicated membership transaction page', function () {
    $cashier = User::factory()->create(['name' => 'Kasir IFGS']);
    $cashier->assignRole('Kasir');

    $memberUser = User::factory()->create(['name' => 'Jane Member']);
    $memberUser->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $product = Product::factory()->create(['name' => 'Aerobic 1 Bulan', 'price' => 120000]);
    $paymentMethod = PaymentMethod::factory()->create(['name' => 'qris']);

    $transaction = Transaction::factory()->create([
        'member_id' => $member->id,
        'user_id' => null,
        'payment_method_id' => $paymentMethod->id,
        'total_amount' => 120000,
        'paid_amount' => 120000,
        'status' => Transaction::STATUS_PENDING,
        'payment_proof' => 'payment_proofs/sample.jpg',
    ]);

    Membership::factory()->create([
        'transaction_id' => $transaction->id,
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
        'price' => 120000,
        'status' => Membership::STATUS_PENDING,
    ]);

    $response = $this->actingAs($cashier)->get(route('membership-transactions.index', ['status' => 'pending']));

    $response->assertOk()
        ->assertSee('Jane Member')
        ->assertSee('Aerobic 1 Bulan')
        ->assertSee('Menunggu Validasi')
        ->assertSee('Transaksi Membership');
});

test('cashier can approve (ACC) pending transaction from membership transactions page', function () {
    $cashier = User::factory()->create(['name' => 'Kasir IFGS']);
    $cashier->assignRole('Kasir');

    $memberUser = User::factory()->create(['name' => 'Jane Member']);
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $product = Product::factory()->create([
        'name' => 'Fitness 1 Bulan',
        'price' => 150000,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
    ]);
    $paymentMethod = PaymentMethod::factory()->create();

    $transaction = Transaction::factory()->create([
        'member_id' => $member->id,
        'user_id' => null,
        'payment_method_id' => $paymentMethod->id,
        'total_amount' => 150000,
        'paid_amount' => 150000,
        'status' => Transaction::STATUS_PENDING,
    ]);

    $membership = Membership::factory()->create([
        'transaction_id' => $transaction->id,
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 150000,
        'status' => Membership::STATUS_PENDING,
    ]);

    $response = $this->actingAs($cashier)->patch(route('membership-transactions.approve', $membership));

    $response->assertRedirect(route('membership-transactions.index'));
    $response->assertSessionHas('success');

    $transaction->refresh();
    expect($transaction->status)->toBe(Transaction::STATUS_COMPLETED);
    expect($transaction->user_id)->toBe($cashier->id);
    expect($transaction->approved_at)->not->toBeNull();

    $membership->refresh();
    expect($membership->status)->toBe(Membership::STATUS_ACTIVE);
    expect($membership->isCurrentlyActive())->toBeTrue();
});

test('cashier can reject pending transaction with reason from membership transactions page', function () {
    $cashier = User::factory()->create(['name' => 'Kasir IFGS']);
    $cashier->assignRole('Kasir');

    $memberUser = User::factory()->create(['name' => 'Jane Member']);
    $member = Member::factory()->create(['user_id' => $memberUser->id]);

    $product = Product::factory()->create(['name' => 'Fitness 1 Bulan']);
    $paymentMethod = PaymentMethod::factory()->create();

    $transaction = Transaction::factory()->create([
        'member_id' => $member->id,
        'user_id' => null,
        'payment_method_id' => $paymentMethod->id,
        'status' => Transaction::STATUS_PENDING,
    ]);

    $membership = Membership::factory()->create([
        'transaction_id' => $transaction->id,
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
        'status' => Membership::STATUS_PENDING,
    ]);

    $response = $this->actingAs($cashier)->patch(route('membership-transactions.reject', $membership), [
        'reason' => 'Nominal transfer kurang Rp 50.000',
    ]);

    $response->assertRedirect(route('membership-transactions.index'));
    $response->assertSessionHas('success');

    $transaction->refresh();
    expect($transaction->status)->toBe(Transaction::STATUS_REJECTED);
    expect($transaction->rejection_reason)->toBe('Nominal transfer kurang Rp 50.000');
    expect($transaction->user_id)->toBe($cashier->id);

    $membership->refresh();
    expect($membership->status)->toBe(Membership::STATUS_REJECTED);
});
