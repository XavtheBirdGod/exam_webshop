<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Enums\Role;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Clean up tenant databases if any exist
    foreach (glob(database_path('tenant*')) as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
});

afterEach(function () {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
    DB::disconnect('tenant');
    Tenant::all()->each->delete();

    foreach (glob(database_path('tenant*')) as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
});

test('only platform admins can access the admin dashboard', function () {
    $admin = User::factory()->create(['role' => Role::PLATFORM_ADMIN]);
    $customer = User::factory()->create(['role' => Role::CUSTOMER]);
    $shopAdmin = User::factory()->create(['role' => Role::SHOP_ADMIN]);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertStatus(200);

    $this->actingAs($customer)
        ->get('/admin/dashboard')
        ->assertStatus(403);

    $this->actingAs($shopAdmin)
        ->get('/admin/dashboard')
        ->assertStatus(403);
});

test('admin dashboard computes active tenants count and aggregates metrics', function () {
    $admin = User::factory()->create(['role' => Role::PLATFORM_ADMIN]);

    $tenant1 = Tenant::create(['id' => 'shop-test-1']);
    $tenant1->domains()->create(['domain' => 'test1.localhost']);

    $tenant2 = Tenant::create(['id' => 'shop-test-2']);
    $tenant2->domains()->create(['domain' => 'test2.localhost']);

    // Seed order in tenant 1
    tenancy()->initialize($tenant1);
    Schema::disableForeignKeyConstraints();
    Order::create([
        'total_amount' => 15000, // 150.00 EUR
        'status' => 'paid',
        'email' => 'customer1@example.com',
        'shipping_address' => [],
        'billing_address' => [],
        'payment_method' => 'stripe',
    ]);
    Schema::enableForeignKeyConstraints();
    tenancy()->end();

    // Seed order in tenant 2
    tenancy()->initialize($tenant2);
    Schema::disableForeignKeyConstraints();
    Order::create([
        'total_amount' => 25000, // 250.00 EUR
        'status' => 'paid',
        'email' => 'customer2@example.com',
        'shipping_address' => [],
        'billing_address' => [],
        'payment_method' => 'stripe',
    ]);
    Schema::enableForeignKeyConstraints();
    tenancy()->end();

    $this->actingAs($admin);

    Livewire::test('admin.dashboard')
        ->assertSee('Platform Overview')
        ->assertSet('selectedVendor', 'all')
        ->assertSet('dateRange', 'all')
        ->assertSee('€400.00') // €150.00 + €250.00
        ->assertSee('2'); // Active boutiques count
});

test('admin dashboard filters by specific boutique/vendor', function () {
    $admin = User::factory()->create(['role' => Role::PLATFORM_ADMIN]);

    $tenant1 = Tenant::create(['id' => 'shop-test-1']);
    $tenant1->domains()->create(['domain' => 'test1.localhost']);

    $tenant2 = Tenant::create(['id' => 'shop-test-2']);
    $tenant2->domains()->create(['domain' => 'test2.localhost']);

    // Seed order in tenant 1
    tenancy()->initialize($tenant1);
    Schema::disableForeignKeyConstraints();
    Order::create([
        'total_amount' => 15000,
        'status' => 'paid',
        'email' => 'customer1@example.com',
        'shipping_address' => [],
        'billing_address' => [],
        'payment_method' => 'stripe',
    ]);
    Schema::enableForeignKeyConstraints();
    tenancy()->end();

    // Seed order in tenant 2
    tenancy()->initialize($tenant2);
    Schema::disableForeignKeyConstraints();
    Order::create([
        'total_amount' => 25000,
        'status' => 'paid',
        'email' => 'customer2@example.com',
        'shipping_address' => [],
        'billing_address' => [],
        'payment_method' => 'stripe',
    ]);
    Schema::enableForeignKeyConstraints();
    tenancy()->end();

    $this->actingAs($admin);

    Livewire::test('admin.dashboard')
        ->set('selectedVendor', 'shop-test-1')
        ->assertSee('€150.00')
        ->assertDontSee('€400.00')
        ->set('selectedVendor', 'shop-test-2')
        ->assertSee('€250.00')
        ->assertDontSee('€150.00');
});

test('admin can view list of all platform users on dashboard', function () {
    $admin = User::factory()->create(['role' => Role::PLATFORM_ADMIN]);
    $user1 = User::factory()->create(['name' => 'John Doe', 'role' => Role::CUSTOMER]);
    $user2 = User::factory()->create(['name' => 'Jane Admin', 'role' => Role::SHOP_ADMIN]);

    $this->actingAs($admin);

    Livewire::test('admin.dashboard')
        ->set('activeTab', 'users')
        ->assertSee('John Doe')
        ->assertSee('Jane Admin')
        ->assertSee('Platform Users');
});

test('admin can create a new boutique location and delete it', function () {
    $admin = User::factory()->create(['role' => Role::PLATFORM_ADMIN]);

    $this->actingAs($admin);

    // 1. Create a new boutique
    Livewire::test('admin.dashboard')
        ->set('activeTab', 'boutiques')
        ->set('newBoutiqueName', 'Brussels')
        ->set('newBoutiqueDomain', 'brussels.localhost')
        ->call('createBoutique')
        ->assertHasNoErrors()
        ->assertSet('newBoutiqueName', '')
        ->assertSet('newBoutiqueDomain', '');

    // Assert Tenant and Domain exist in DB
    $tenant = Tenant::find('shop-brussels');
    expect($tenant)->not->toBeNull();
    expect($tenant->domains()->where('domain', 'brussels.localhost')->exists())->toBeTrue();

    // Verify products seeded inside tenant DB
    tenancy()->initialize($tenant);
    expect(\App\Models\Category::where('slug', 'body-care')->exists())->toBeTrue();
    expect(\App\Models\Product::where('slug', 'ritual-of-sakura-body-cream')->exists())->toBeTrue();
    expect(\App\Models\Shop::where('name', 'Brussels')->exists())->toBeTrue();
    tenancy()->end();

    // 2. Delete the boutique
    Livewire::test('admin.dashboard')
        ->set('activeTab', 'boutiques')
        ->call('deleteBoutique', 'shop-brussels')
        ->assertHasNoErrors();

    expect(Tenant::find('shop-brussels'))->toBeNull();
});

