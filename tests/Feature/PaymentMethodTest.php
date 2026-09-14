<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
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

test('authorized Admin/Manager and Kasir can access /payment-methods without cards or penggunaan column', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $response = $this->actingAs($admin)->get(route('payment-methods.index'));

    $response->assertOk()
        ->assertSee('Manajemen /')
        ->assertSee('Metode Pembayaran')
        ->assertSee('Informasi Pembayaran')
        ->assertDontSee('Total Metode')
        ->assertDontSee('Semua metode pembayaran')
        ->assertDontSee('Penggunaan');

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

test('PaymentMethodSeeder populates Tunai, Transfer Bank, and QRIS with details', function () {
    $this->seed(PaymentMethodSeeder::class);

    $cash = PaymentMethod::where('code', 'cash')->first();
    expect($cash)->not->toBeNull();
    expect($cash->type)->toBe('cash');

    $bank = PaymentMethod::where('code', 'bank_transfer')->first();
    expect($bank)->not->toBeNull();
    expect($bank->type)->toBe('bank_transfer');
    expect($bank->account_number)->toBe('123-456-7890 (BCA)');

    $qris = PaymentMethod::where('code', 'qris')->first();
    expect($qris)->not->toBeNull();
    expect($qris->type)->toBe('qris');
    expect($qris->qr_image_url)->not->toBeNull();
});

test('can create a new payment method with auto-generated code from name', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('payment-methods.store'), [
        'name' => 'Transfer Bank BCA',
        'type' => PaymentMethod::TYPE_BANK_TRANSFER,
        'account_number' => '5420123456',
        'account_name' => 'PT Indo Fitness Gym',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('payment_methods', [
        'name' => 'Transfer Bank BCA',
        'code' => 'transfer_bank_bca',
        'type' => 'bank_transfer',
        'account_number' => '5420123456',
        'account_name' => 'PT Indo Fitness Gym',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);
});

test('can create payment method with qris image upload', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $file = UploadedFile::fake()->image('my-qris.png');

    $response = $this->actingAs($admin)->post(route('payment-methods.store'), [
        'name' => 'QRIS Kasir Utama',
        'type' => PaymentMethod::TYPE_QRIS,
        'account_name' => 'Indo Fitness Gym Sport',
        'qr_image' => $file,
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response->assertRedirect(route('payment-methods.index'));

    $pm = PaymentMethod::where('name', 'QRIS Kasir Utama')->first();
    expect($pm)->not->toBeNull();
    expect($pm->code)->toBe('qris_kasir_utama');
    expect($pm->qr_image)->not->toBeNull();
    Storage::disk('public')->assertExists($pm->qr_image);
});

test('can update an existing payment method and auto-updates code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Mandiri Lama',
        'code' => 'mandiri_lama',
        'type' => 'bank_transfer',
        'account_number' => '11112222',
        'account_name' => 'Indo Fitness',
        'status' => PaymentMethod::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($admin)->put(route('payment-methods.update', $paymentMethod), [
        'name' => 'Bank Mandiri Baru',
        'type' => 'bank_transfer',
        'account_number' => '99998888',
        'account_name' => 'Indo Fitness Gym Sport',
        'status' => PaymentMethod::STATUS_INACTIVE,
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    $paymentMethod->refresh();

    expect($paymentMethod->name)->toBe('Bank Mandiri Baru');
    expect($paymentMethod->code)->toBe('bank_mandiri_baru');
    expect($paymentMethod->account_number)->toBe('99998888');
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

test('payment methods index displays payment values correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    PaymentMethod::factory()->create([
        'name' => 'BCA Rekening',
        'type' => 'bank_transfer',
        'account_number' => '7700112233',
        'account_name' => 'Indo Fitness',
    ]);

    PaymentMethod::factory()->create([
        'name' => 'QRIS Kasir',
        'type' => 'qris',
        'qr_image' => 'img/qris-ifgs.svg',
    ]);

    $response = $this->actingAs($admin)->get(route('payment-methods.index'));

    $response->assertOk()
        ->assertSee('7700112233')
        ->assertSee('a.n. Indo Fitness')
        ->assertSee('Lihat QRIS')
        ->assertSee('modalPreviewQris');
});

test('payment methods index can be filtered by search term and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    PaymentMethod::factory()->create(['name' => 'Kartu Kredit', 'code' => 'credit_card', 'status' => PaymentMethod::STATUS_ACTIVE]);
    PaymentMethod::factory()->create(['name' => 'LinkAja Khusus', 'code' => 'linkaja_khusus', 'status' => PaymentMethod::STATUS_INACTIVE]);

    // Search
    $resSearch = $this->actingAs($admin)->get(route('payment-methods.index', ['search' => 'LinkAja']));
    $resSearch->assertOk();
    $resSearch->assertSee('LinkAja Khusus');
    $resSearch->assertDontSee('Kartu Kredit');

    // Filter status
    $resActive = $this->actingAs($admin)->get(route('payment-methods.index', ['status' => 'active']));
    $resActive->assertOk();
    $resActive->assertSee('Kartu Kredit');
    $resActive->assertDontSee('LinkAja Khusus');
});
