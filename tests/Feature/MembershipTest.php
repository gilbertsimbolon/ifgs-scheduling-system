<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('authorized Admin/Manager and Kasir can access /memberships', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $kasir = User::factory()->create();
    $kasir->assignRole('Kasir');

    $this->actingAs($admin)->get(route('memberships.index'))->assertOk();
    $this->actingAs($kasir)->get(route('memberships.index'))->assertOk();
});

test('unauthorized Member role receives 403 on /memberships', function () {
    $memberUser = User::factory()->create();
    $memberUser->assignRole('Member');

    $this->actingAs($memberUser)->get(route('memberships.index'))->assertForbidden();
});

test('unauthenticated guest is redirected to login from /memberships', function () {
    $this->get(route('memberships.index'))->assertRedirect(route('login'));
});

test('relationships between User, Member, Membership, and Product work correctly', function () {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $member = Member::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create([
        'name' => 'Paket Gym 1 Bulan',
        'price' => 150000,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
    ]);

    $membership = Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'price' => $product->price,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
    ]);

    // Member belongs to user
    expect($member->user->id)->toBe($user->id);
    // User has one member
    expect($user->member->id)->toBe($member->id);
    // Member has many memberships
    expect($member->memberships)->toHaveCount(1);
    expect($member->memberships->first()->id)->toBe($membership->id);
    // Membership belongs to member and product
    expect($membership->member->id)->toBe($member->id);
    expect($membership->product->id)->toBe($product->id);
    // Member name is accessible via membership->member->user->name
    expect($membership->member->user->name)->toBe('Budi Santoso');
    // Product has many memberships
    expect($product->memberships)->toHaveCount(1);
});

test('membership snapshots product price at creation and does not change when product price changes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $member = Member::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Paket Gym 1 Bulan',
        'price' => 150000.00,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
        'status' => Product::STATUS_ACTIVE,
    ]);
    $pm = PaymentMethod::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)->post(route('memberships.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $pm->id,
        'start_date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('memberships.index'));

    $membership = Membership::where('member_id', $member->id)->first();
    expect($membership)->not->toBeNull();
    expect((float) $membership->price)->toBe(150000.00);
    expect($membership->payment_method_id)->toBe($pm->id);

    // Update master product price to 200.000
    $product->update(['price' => 200000.00]);

    // Membership price must remain 150.000 (snapshot)
    $membership->refresh();
    expect((float) $membership->price)->toBe(150000.00);
});

test('auto calculates end_date when end_date is omitted in store', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $member = Member::factory()->create();
    $product = Product::factory()->create([
        'duration_value' => 3,
        'duration_unit' => Product::DURATION_MONTH,
        'status' => Product::STATUS_ACTIVE,
    ]);
    $pm = PaymentMethod::factory()->create(['status' => 'active']);

    $startDate = '2026-03-01';
    $expectedEndDate = '2026-06-01';

    $this->actingAs($admin)->post(route('memberships.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $pm->id,
        'start_date' => $startDate,
    ]);

    $membership = Membership::where('member_id', $member->id)->first();
    expect($membership->end_date->format('Y-m-d'))->toBe($expectedEndDate);
});

test('cannot create membership with an inactive product', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $member = Member::factory()->create();
    $inactiveProduct = Product::factory()->inactive()->create();
    $pm = PaymentMethod::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)->from(route('memberships.index'))->post(route('memberships.store'), [
        'member_id' => $member->id,
        'product_id' => $inactiveProduct->id,
        'payment_method_id' => $pm->id,
        'start_date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('memberships.index'));
    $response->assertSessionHas('error');
    expect(Membership::count())->toBe(0);
});

test('cannot create membership with an inactive payment method', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $member = Member::factory()->create();
    $product = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
    $inactivePm = PaymentMethod::factory()->inactive()->create();

    $response = $this->actingAs($admin)->post(route('memberships.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $inactivePm->id,
        'start_date' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('payment_method_id');
    expect(Membership::count())->toBe(0);
});

test('validates end_date must be after or equal to start_date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $member = Member::factory()->create();
    $product = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
    $pm = PaymentMethod::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)->post(route('memberships.store'), [
        'member_id' => $member->id,
        'product_id' => $product->id,
        'payment_method_id' => $pm->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-01',
    ]);

    $response->assertSessionHasErrors('end_date');
    expect(Membership::count())->toBe(0);
});

test('membership status checks and activeMembership helper work correctly', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();

    // Active membership
    $activeMembership = Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
        'start_date' => now()->subDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(25)->format('Y-m-d'),
        'status' => Membership::STATUS_ACTIVE,
    ]);

    expect($activeMembership->isCurrentlyActive())->toBeTrue();
    expect($member->activeMembership()->id)->toBe($activeMembership->id);

    // Cancelled membership
    $activeMembership->update(['status' => Membership::STATUS_CANCELLED]);
    expect($activeMembership->isCurrentlyActive())->toBeFalse();
    expect($member->activeMembership())->toBeNull();
});

test('can cancel a membership via PATCH /memberships/{id}/cancel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $membership = Membership::factory()->create(['status' => Membership::STATUS_ACTIVE]);

    $response = $this->actingAs($admin)->patch(route('memberships.cancel', $membership));

    $response->assertRedirect(route('memberships.index'));
    expect($membership->fresh()->status)->toBe(Membership::STATUS_CANCELLED);
});

test('can delete a membership via DELETE /memberships/{id}', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $membership = Membership::factory()->create();

    $response = $this->actingAs($admin)->delete(route('memberships.destroy', $membership));

    $response->assertRedirect(route('memberships.index'));
    expect(Membership::find($membership->id))->toBeNull();
});

test('calculateEndDate AJAX endpoint returns correct calculated date and price', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $product = Product::factory()->create([
        'price' => 250000.00,
        'duration_value' => 2,
        'duration_unit' => Product::DURATION_MONTH,
    ]);

    $response = $this->actingAs($admin)->getJson(route('memberships.calculate-end-date', [
        'product_id' => $product->id,
        'start_date' => '2026-01-15',
    ]));

    $response->assertOk();
    $response->assertJson([
        'end_date' => '2026-03-15',
        'price' => 250000.00,
    ]);
});

test('product CRUD operations and prevention of deleting used product', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    // Create product
    $response = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Paket Pilates',
        'description' => 'Akses kelas pilates mingguan',
        'price' => 200000,
        'duration_value' => 1,
        'duration_unit' => 'month',
        'status' => 'active',
    ]);
    $response->assertRedirect(route('products.index'));

    $product = Product::where('name', 'Paket Pilates')->first();
    expect($product)->not->toBeNull();

    // Toggle status
    $this->actingAs($admin)->patch(route('products.toggle-status', $product));
    expect($product->fresh()->status)->toBe(Product::STATUS_INACTIVE);

    // Can delete if unused
    $this->actingAs($admin)->delete(route('products.destroy', $product));
    expect(Product::find($product->id))->toBeNull();

    // Cannot delete if used by membership
    $usedProduct = Product::factory()->create(['status' => 'active']);
    Membership::factory()->create(['product_id' => $usedProduct->id]);

    $delResponse = $this->actingAs($admin)->delete(route('products.destroy', $usedProduct));
    $delResponse->assertRedirect(route('products.index'));
    $delResponse->assertSessionHas('error');
    expect(Product::find($usedProduct->id))->not->toBeNull();
});

test('membership index can be filtered dynamically by product_id', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $productA = Product::factory()->create(['name' => 'Paket Platinum VIP', 'price' => 500000]);
    $productB = Product::factory()->create(['name' => 'Paket Reguler Siang', 'price' => 100000]);

    $userA = User::factory()->create(['name' => 'Member Platinum User']);
    $userA->assignRole('Member');
    $memberA = Member::factory()->create(['user_id' => $userA->id]);

    $userB = User::factory()->create(['name' => 'Member Reguler User']);
    $userB->assignRole('Member');
    $memberB = Member::factory()->create(['user_id' => $userB->id]);
    $pm = PaymentMethod::factory()->create();

    $membershipA = Membership::create([
        'member_id' => $memberA->id,
        'product_id' => $productA->id,
        'payment_method_id' => $pm->id,
        'price' => $productA->price,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
        'status' => 'active',
    ]);

    $membershipB = Membership::create([
        'member_id' => $memberB->id,
        'product_id' => $productB->id,
        'payment_method_id' => $pm->id,
        'price' => $productB->price,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
        'status' => 'active',
    ]);

    // View displays both products in dropdown dynamically
    $responseIndex = $this->actingAs($admin)->get(route('memberships.index'));
    $responseIndex->assertOk();
    $responseIndex->assertSee('Paket Platinum VIP');
    $responseIndex->assertSee('Paket Reguler Siang');

    // Filter by Product A
    $responseFilterA = $this->actingAs($admin)->get(route('memberships.index', ['product_id' => $productA->id]));
    $responseFilterA->assertOk();
    $responseFilterA->assertViewHas('memberships', function ($m) use ($membershipA, $membershipB) {
        return $m->contains($membershipA) && ! $m->contains($membershipB);
    });

    // Filter by Product B
    $responseFilterB = $this->actingAs($admin)->get(route('memberships.index', ['product_id' => $productB->id]));
    $responseFilterB->assertOk();
    $responseFilterB->assertViewHas('memberships', function ($m) use ($membershipA, $membershipB) {
        return $m->contains($membershipB) && ! $m->contains($membershipA);
    });
});

test('membership index displays filtered empty state when filter finds nothing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $product = Product::factory()->create(['name' => 'Paket Ada']);
    $user = User::factory()->create(['name' => 'Member Ada']);
    $user->assignRole('Member');
    $member = Member::factory()->create(['user_id' => $user->id]);
    Membership::factory()->create([
        'member_id' => $member->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($admin)->get(route('memberships.index', ['search' => 'TidakAdaKeyword']));
    $response->assertOk();
    $response->assertSee('Tidak ada data membership yang ditemukan');
    $response->assertSee('Coba ubah kata kunci pencarian atau bersihkan filter.');
});

test('can update membership and change payment method', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $pm1 = PaymentMethod::factory()->create(['name' => 'Metode Lama']);
    $pm2 = PaymentMethod::factory()->create(['name' => 'Metode Baru']);

    $membership = Membership::factory()->create([
        'payment_method_id' => $pm1->id,
        'price' => 100000,
    ]);

    $response = $this->actingAs($admin)->put(route('memberships.update', $membership), [
        'product_id' => $membership->product_id,
        'payment_method_id' => $pm2->id,
        'start_date' => $membership->start_date->format('Y-m-d'),
        'end_date' => $membership->end_date->format('Y-m-d'),
        'price' => 120000,
        'status' => 'active',
    ]);

    $response->assertRedirect(route('memberships.index'));
    $membership->refresh();

    expect($membership->payment_method_id)->toBe($pm2->id);
    expect((float) $membership->price)->toBe(120000.00);
});
