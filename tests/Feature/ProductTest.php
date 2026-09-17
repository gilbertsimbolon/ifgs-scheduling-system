<?php

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
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

test('admin can access product index and see clean total member link to membership filter', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $product = Product::factory()->create([
        'name' => 'Fitness 1 Bulan',
        'price' => 150000,
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_MONTH,
    ]);

    $response = $this->actingAs($admin)->get(route('products.index'));

    $response->assertOk()
        ->assertSee('Fitness 1 Bulan')
        ->assertSee('Total Member')
        ->assertSee(route('memberships.index', ['product_id' => $product->id]))
        ->assertDontSee('badge bg-label-primary font-monospace')
        ->assertDontSee('Transaksi</span>')
        ->assertDontSee('Member Terdaftar');
});

test('product seeder seeds all 7 packages from poster correctly', function () {
    $this->seed(ProductSeeder::class);

    expect(Product::count())->toBe(7);

    expect(Product::where('name', 'Fitness 1 Bulan')->first())->not->toBeNull();
    expect(Product::where('name', 'Fitness 2 Bulan')->first())->not->toBeNull();
    expect(Product::where('name', 'Fitness Visit')->first())->not->toBeNull();
    expect(Product::where('name', 'Aerobic / Zumba 1 Bulan')->first())->not->toBeNull();
    expect(Product::where('name', 'Aerobic / Zumba 2 Bulan')->first())->not->toBeNull();
    expect(Product::where('name', 'Aerobic / Zumba Visit')->first())->not->toBeNull();
    expect(Product::where('name', 'Aerobic + Fitness')->first())->not->toBeNull();

    $fitnessVisit = Product::where('name', 'Fitness Visit')->first();
    expect((int) $fitnessVisit->price)->toBe(25000);
    expect($fitnessVisit->duration_value)->toBe(1);
    expect($fitnessVisit->duration_unit)->toBe('day');
    expect($fitnessVisit->duration_formatted)->toBe('1 Hari (Visit)');
});

test('calculateEndDate for 1 day visit returns the exact same day', function () {
    $product = Product::factory()->create([
        'name' => 'Fitness Visit',
        'duration_value' => 1,
        'duration_unit' => Product::DURATION_DAY,
    ]);

    $startDate = '2026-09-14';
    $calculatedEnd = $product->calculateEndDate($startDate)->format('Y-m-d');

    // Visit ends on the same date (until closing time of same day)
    expect($calculatedEnd)->toBe('2026-09-14');
});

test('membership page filters by product_id correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin/Manager');

    $productA = Product::factory()->create(['name' => 'Fitness 1 Bulan']);
    $productB = Product::factory()->create(['name' => 'Aerobic Visit']);

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
