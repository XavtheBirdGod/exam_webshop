<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use App\Enums\Role;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
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

test('database records are completely isolated between tenants', function () {
    $tenantA = Tenant::create(['id' => 'shop-a']);
    $tenantA->domains()->create(['domain' => 'a.localhost']);

    $tenantB = Tenant::create(['id' => 'shop-b']);
    $tenantB->domains()->create(['domain' => 'b.localhost']);

    // Initialize Tenant A and create a product & category
    tenancy()->initialize($tenantA);
    $categoryA = Category::create(['name' => 'Fragrance', 'slug' => 'fragrance']);
    $productA = Product::create([
        'category_id' => $categoryA->id,
        'name' => 'Amsterdam Scented Sticks',
        'slug' => 'amsterdam-scents',
        'price' => 2990,
        'status' => 'active',
        'featured' => true,
    ]);
    expect(Category::count())->toBe(1);
    expect(Product::count())->toBe(1);
    tenancy()->end();

    // Initialize Tenant B and assert it has no categories or products
    tenancy()->initialize($tenantB);
    expect(Category::count())->toBe(0);
    expect(Product::count())->toBe(0);

    // Create records on Tenant B
    $categoryB = Category::create(['name' => 'Body Care', 'slug' => 'body-care']);
    $productB = Product::create([
        'category_id' => $categoryB->id,
        'name' => 'Paris Scrub',
        'slug' => 'paris-scrub',
        'price' => 1990,
        'status' => 'active',
        'featured' => false,
    ]);
    expect(Category::count())->toBe(1);
    expect(Product::count())->toBe(1);
    expect(Product::first()->name)->toBe('Paris Scrub');
    tenancy()->end();

    // Go back to Tenant A and verify it still has its original records
    tenancy()->initialize($tenantA);
    expect(Category::count())->toBe(1);
    expect(Product::count())->toBe(1);
    expect(Product::first()->name)->toBe('Amsterdam Scented Sticks');
    tenancy()->end();
});

test('shop admin of tenant A is forbidden from tenant B backoffice', function () {
    $tenantA = Tenant::create(['id' => 'shop-a']);
    $tenantA->domains()->create(['domain' => 'a.localhost']);

    $tenantB = Tenant::create(['id' => 'shop-b']);
    $tenantB->domains()->create(['domain' => 'b.localhost']);

    $adminA = User::factory()->create(['role' => Role::SHOP_ADMIN]);
    $adminA->tenants()->attach($tenantA->id, ['role' => Role::SHOP_ADMIN->value]);

    // Accessing Tenant A backoffice as Admin A works
    tenancy()->initialize($tenantA);
    $this->actingAs($adminA)
        ->get('http://a.localhost/seller/dashboard')
        ->assertStatus(200);
    tenancy()->end();

    // Accessing Tenant B backoffice as Admin A is Forbidden (403)
    tenancy()->initialize($tenantB);
    $this->actingAs($adminA)
        ->get('http://b.localhost/seller/dashboard')
        ->assertStatus(403);
    tenancy()->end();
});
