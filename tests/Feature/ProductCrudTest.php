<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Enums\Role;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;

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

test('non-seller users are blocked from backoffice', function () {
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);
    $customer = User::factory()->create(['role' => Role::CUSTOMER]);

    tenancy()->initialize($tenant);

    actingAs($customer)
        ->get('http://test-shop.localhost/seller/dashboard')
        ->assertStatus(403);

    tenancy()->end();
});

test('shop admins can access dashboard and product list', function () {
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);
    $user = User::factory()->create(['role' => Role::CUSTOMER]);
    $user->tenants()->attach($tenant->id, ['role' => Role::SHOP_ADMIN]);

    tenancy()->initialize($tenant);

    actingAs($user)
        ->get('http://test-shop.localhost/seller/dashboard')
        ->assertStatus(200)
        ->assertSee('Boutique Dashboard');

    actingAs($user)
        ->get('http://test-shop.localhost/seller/products')
        ->assertStatus(200)
        ->assertSee('Manage Products');

    tenancy()->end();
});

test('shop admins can create a product with variants', function () {
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);
    $user = User::factory()->create(['role' => Role::CUSTOMER]);
    $user->tenants()->attach($tenant->id, ['role' => Role::SHOP_ADMIN]);

    tenancy()->initialize($tenant);

    $category = Category::create(['name' => 'Skin', 'slug' => 'skin']);

    Livewire::actingAs($user)
        ->test('seller.products.create')
        ->set('form.name', 'Body Cream')
        ->set('form.description', 'Luxury body cream')
        ->set('form.price', 19.99)
        ->set('form.category_id', $category->id)
        ->set('form.variants', [
            [
                'name' => 'Size',
                'value' => '200 ml',
                'sku' => 'BC-200',
                'price_modifier' => 5.00,
                'stock_on_hand' => 50,
            ]
        ])
        ->call('save')
        ->assertRedirect('/seller/products');

    expect(Product::count())->toBe(1);
    expect(ProductVariant::count())->toBe(1);

    $product = Product::first();
    $variant = ProductVariant::first();

    expect($product->price)->toBe(1999); // Cents
    expect($variant->price_modifier)->toBe(500); // Cents
    expect($variant->stock_available)->toBe(50);
    expect($variant->stockMovements()->count())->toBe(1);

    tenancy()->end();
});
