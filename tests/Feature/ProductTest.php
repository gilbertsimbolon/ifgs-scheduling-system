<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductDuration;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
});

test('admin can access product index and see clean total member link and duration count without harga column', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $product = Product::factory()->create([
        'name' => 'Fitness',
        'status' => Product::STATUS_ACTIVE,
    ]);

    $product->durations()->delete();
    $product->durations()->createMany([
        ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_DAY, 'price' => 25000, 'is_active' => true],
        ['duration_value' => 1, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 150000, 'is_active' => true],
        ['duration_value' => 2, 'duration_unit' => ProductDuration::DURATION_MONTH, 'price' => 250000, 'is_active' => true],
    ]);

    $response = $this->actingAs($admin)->get(route('products.index'));

    $response->assertOk()
        ->assertSee('Fitness')
        ->assertSee('3 Pilihan Durasi')
        ->assertSee('Total Member')
        ->assertSee(route('memberships.index', ['product_id' => $product->id]));

    // Pastikan thead tabel utama tidak memiliki kolom Harga
    preg_match('/<table class="table table-hover">.*?<thead class="table-light">(.*?)<\/thead>/s', $response->getContent(), $matches);
    expect($matches[1] ?? '')->not->toContain('Harga');
});

test('product seeder seeds 3 consolidated packages with durations correctly', function () {
    $this->seed(ProductSeeder::class);

    expect(Product::count())->toBe(3);

    $fitness = Product::where('name', 'Fitness')->first();
    expect($fitness)->not->toBeNull();
    expect($fitness->durations()->count())->toBe(3);

    $aerobic = Product::where('name', 'Aerobic / Zumba')->first();
    expect($aerobic)->not->toBeNull();
    expect($aerobic->durations()->count())->toBe(3);

    $combo = Product::where('name', 'Aerobic + Fitness')->first();
    expect($combo)->not->toBeNull();
    expect($combo->status)->toBe(Product::STATUS_INACTIVE);

    $dayVisit = $fitness->durations()->where('duration_unit', ProductDuration::DURATION_DAY)->first();
    expect((int) $dayVisit->price)->toBe(25000);
    expect($dayVisit->duration_formatted)->toBe('1 Hari (Visit)');
});

test('admin can create a new product with multiple durations including lifetime', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Premium VIP Gym',
        'description' => 'Akses eksklusif VIP',
        'status' => 'active',
        'durations' => [
            ['duration_value' => 1, 'duration_unit' => 'month', 'price' => 150000],
            ['duration_value' => 2, 'duration_unit' => 'month', 'price' => 250000],
            ['duration_value' => 1, 'duration_unit' => 'day', 'price' => 25000],
            ['duration_value' => 0, 'duration_unit' => 'lifetime', 'price' => 1500000],
        ],
    ]);

    $response->assertRedirect(route('products.index'));
    $response->assertSessionHas('success');

    $created = Product::where('name', 'Premium VIP Gym')->first();
    expect($created)->not->toBeNull();
    expect($created->durations()->count())->toBe(4);

    $lifetime = $created->durations()->where('duration_unit', 'lifetime')->first();
    expect($lifetime)->not->toBeNull();
    expect($lifetime->duration_formatted)->toBe('Seumur Hidup');
    expect((int) $lifetime->price)->toBe(1500000);

    // Test calculateEndDate for lifetime
    $calculated = $lifetime->calculateEndDate('2026-09-28');
    expect($calculated->year)->toBe(2125);
});

test('product creation rejects duplicate duration combination', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $response = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Duplicated Gym',
        'status' => 'active',
        'durations' => [
            ['duration_value' => 1, 'duration_unit' => 'month', 'price' => 150000],
            ['duration_value' => 1, 'duration_unit' => 'month', 'price' => 160000],
        ],
    ]);

    $response->assertSessionHasErrors(['durations']);
    expect(Product::where('name', 'Duplicated Gym')->exists())->toBeFalse();
});

test('admin can update a product and modify its durations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $product = Product::factory()->create(['name' => 'Standard Fitness']);
    $d1 = $product->durations()->first();

    $response = $this->actingAs($admin)->put(route('products.update', $product), [
        'name' => 'Standard Fitness Updated',
        'status' => 'active',
        'durations' => [
            ['id' => $d1->id, 'duration_value' => 1, 'duration_unit' => 'month', 'price' => 175000],
            ['duration_value' => 2, 'duration_unit' => 'month', 'price' => 300000],
        ],
    ]);

    $response->assertRedirect(route('products.index'));

    $product->refresh();
    expect($product->name)->toBe('Standard Fitness Updated');
    expect($product->durations()->count())->toBe(2);
    expect((int) $d1->fresh()->price)->toBe(175000);
});

test('calculateEndDate for 1 day visit returns the exact same day', function () {
    $product = Product::factory()->create(['name' => 'Fitness Visit']);
    $duration = $product->durations()->first();
    $duration->update([
        'duration_value' => 1,
        'duration_unit' => ProductDuration::DURATION_DAY,
        'price' => 25000,
        'is_active' => true,
    ]);

    $startDate = '2026-09-14';
    $calculatedEnd = $duration->calculateEndDate($startDate)->format('Y-m-d');

    // Visit ends on the same date (until closing time of same day)
    expect($calculatedEnd)->toBe('2026-09-14');
});

test('membership page filters by product_id correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $productA = Product::factory()->create(['name' => 'Fitness']);
    $productB = Product::factory()->create(['name' => 'Aerobic']);

    $userA = User::factory()->create(['name' => 'Member Fitness']);
    $memberA = Member::factory()->create(['user_id' => $userA->id]);

    $userB = User::factory()->create(['name' => 'Member Aerobic']);
    $memberB = Member::factory()->create(['user_id' => $userB->id]);

    $paymentMethod = PaymentMethod::factory()->create(['name' => 'cash']);

    Membership::factory()->create([
        'member_id' => $memberA->id,
        'product_id' => $productA->id,
        'payment_method_id' => $paymentMethod->id,
        'price' => 150000,
        'start_date' => '2026-09-01',
        'end_date' => '2026-10-01',
    ]);

    Membership::factory()->create([
        'member_id' => $memberB->id,
        'product_id' => $productB->id,
        'payment_method_id' => $paymentMethod->id,
        'price' => 25000,
        'start_date' => '2026-09-14',
        'end_date' => '2026-09-14',
    ]);

    $response = $this->actingAs($admin)->get(route('memberships.index', ['product_id' => $productA->id]));

    $response->assertOk();
    $viewMemberships = $response->viewData('memberships');
    expect($viewMemberships->total())->toBe(1);
    expect($viewMemberships->first()->member->user->name)->toBe('Member Fitness');
});
