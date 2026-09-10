<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authorized Admin/Manager and Kasir can access /payment-methods', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($admin)->get(route('payment-methods.index'))->assertOk();
    $this->actingAs($admin)->get(route('payment-methods.index'))
        ->assertOk()
        ->assertSee('Manajemen /')
        ->assertSee('Metode Pembayaran');
    $this->actingAs($kasir)->get(route('payment-methods.index'))->assertOk();
});

test('unauthorized Member role receives 403 on /payment-methods', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $this->actingAs($memberUser)->get(route('payment-methods.index'))->assertForbidden();
});

test('unauthenticated guest is redirected to login from /payment-methods', function () {
    $this->get(route('payment-methods.index'))->assertRedirect(route('login'));
});

test('PaymentMethodSeeder populates Tunai, Transfer Bank, and QRIS as active', function () {
    $this->seed(PaymentMethodSeeder::class);

    expect(PaymentMethod::where('code', 'cash')->where('status', PaymentMethod::STATUS_ACTIVE)->exists())->toBeTrue();
    expect(PaymentMethod::where('code', 'bank_transfer')->where('status', PaymentMethod::STATUS_ACTIVE)->exists())->toBeTrue();
    expect(PaymentMethod::where('code', 'qris')->where('status', PaymentMethod::STATUS_ACTIVE)->exists())->toBeTrue();
});

test('can create a new payment method with valid data', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('payment-methods.store'), [
        'name' => 'Debit BCA',
        'code' => 'debit_bca',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('payment_methods', [
        'name' => 'Debit BCA',
        'code' => 'debit_bca',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);
});

test('auto-generates code if code is empty in store', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('payment-methods.store'), [
        'name' => 'GoPay Instant',
        'code' => '',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    $this->assertDatabaseHas('payment_methods', [
        'name' => 'GoPay Instant',
        'code' => 'gopay_instant',
    ]);
});

test('code must be unique across payment methods', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    PaymentMethod::factory()->create(['code' => 'existing_code']);

    $response = $this->actingAs($admin)->post(route('payment-methods.store'), [
        'name' => 'Duplicate Code Test',
        'code' => 'existing_code',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response->assertSessionHasErrors('code');
});

test('can update an existing payment method', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Old Name',
        'code' => 'old_code',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($admin)->put(route('payment-methods.update', $paymentMethod), [
        'name' => 'Updated Name',
        'code' => 'updated_code',
        'status' => PaymentMethod::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    $paymentMethod->refresh();

    expect($paymentMethod->name)->toBe('Updated Name');
    expect($paymentMethod->code)->toBe('updated_code');
    expect($paymentMethod->status)->toBe(PaymentMethod::STATUS_INACTIVE);
});

test('can toggle payment method status via patch request', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $paymentMethod = PaymentMethod::factory()->create(['status' => PaymentMethod::STATUS_ACTIVE]);

    $response = $this->actingAs($admin)->patchJson(route('payment-methods.toggle-status', $paymentMethod));

    $response->assertOk();
    $response->assertJson(['success' => true, 'status' => PaymentMethod::STATUS_INACTIVE]);

    $paymentMethod->refresh();
    expect($paymentMethod->status)->toBe(PaymentMethod::STATUS_INACTIVE);

    // Toggle back
    $response2 = $this->actingAs($admin)->patchJson(route('payment-methods.toggle-status', $paymentMethod));
    $response2->assertOk();
    $response2->assertJson(['success' => true, 'status' => PaymentMethod::STATUS_ACTIVE]);

    $paymentMethod->refresh();
    expect($paymentMethod->status)->toBe(PaymentMethod::STATUS_ACTIVE);
});

test('can delete payment method if never used in memberships', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $paymentMethod = PaymentMethod::factory()->create();

    $response = $this->actingAs($admin)->delete(route('payment-methods.destroy', $paymentMethod));

    $response->assertRedirect(route('payment-methods.index'));
    $response->assertSessionHas('success');
    expect(PaymentMethod::find($paymentMethod->id))->toBeNull();
});

test('cannot delete payment method if already used in memberships', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $paymentMethod = PaymentMethod::factory()->create();
    $member = Member::factory()->create();
    $product = Product::factory()->create();

    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('payment-methods.destroy', $paymentMethod));

    $response->assertRedirect(route('payment-methods.index'));
    $response->assertSessionHas('error');
    expect(PaymentMethod::find($paymentMethod->id))->not->toBeNull();
});

test('relationship between PaymentMethod and Membership works correctly', function () {
    $paymentMethod = PaymentMethod::factory()->create();
    $membership = Membership::factory()->create(['payment_method_id' => $paymentMethod->id]);

    expect($paymentMethod->memberships)->toHaveCount(1);
    expect($paymentMethod->memberships->first()->id)->toBe($membership->id);
    expect($membership->paymentMethod->id)->toBe($paymentMethod->id);
});

test('payment methods index can be filtered by search term and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $pm1 = PaymentMethod::factory()->create(['name' => 'Kartu Kredit', 'code' => 'credit_card', 'status' => PaymentMethod::STATUS_ACTIVE]);
    $pm2 = PaymentMethod::factory()->create(['name' => 'ShopeePay', 'code' => 'shopeepay', 'status' => PaymentMethod::STATUS_INACTIVE]);

    // Search
    $resSearch = $this->actingAs($admin)->get(route('payment-methods.index', ['search' => 'Shopee']));
    $resSearch->assertOk();
    $resSearch->assertSee('ShopeePay');
    $resSearch->assertDontSee('Kartu Kredit');

    // Filter status
    $resActive = $this->actingAs($admin)->get(route('payment-methods.index', ['status' => 'active']));
    $resActive->assertOk();
    $resActive->assertSee('Kartu Kredit');
    $resActive->assertDontSee('ShopeePay');
});
