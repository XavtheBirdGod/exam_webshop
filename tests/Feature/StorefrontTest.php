<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Livewire\Livewire;
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

test('storefront home page renders featured active products', function () {
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);

    tenancy()->initialize($tenant);

    $category = Category::create(['name' => 'Skin', 'slug' => 'skin']);
    
    // Create active featured product
    $product1 = Product::create([
        'category_id' => $category->id,
        'name' => 'Featured Cream',
        'slug' => 'featured-cream',
        'description' => 'A featured cream description',
        'price' => 1000,
        'status' => 'active',
        'featured' => true,
    ]);

    // Create active non-featured product
    $product2 = Product::create([
        'category_id' => $category->id,
        'name' => 'Standard Soap',
        'slug' => 'standard-soap',
        'description' => 'A standard soap description',
        'price' => 500,
        'status' => 'active',
        'featured' => false,
    ]);

    Livewire::test('shop.home')
        ->assertSee('Featured Cream')
        ->assertDontSee('Standard Soap');

    tenancy()->end();
});

test('storefront products page lists and filters products', function () {
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);

    tenancy()->initialize($tenant);

    $catSkin = Category::create(['name' => 'Skin', 'slug' => 'skin']);
    $catHome = Category::create(['name' => 'Home', 'slug' => 'home']);

    Product::create([
        'category_id' => $catSkin->id,
        'name' => 'Body Wash',
        'slug' => 'body-wash',
        'price' => 1200,
        'status' => 'active',
        'featured' => false,
    ]);

    Product::create([
        'category_id' => $catHome->id,
        'name' => 'Candle Scented',
        'slug' => 'candle-scented',
        'price' => 2400,
        'status' => 'active',
        'featured' => false,
    ]);

    // Test search
    Livewire::test('shop.products')
        ->set('search', 'Wash')
        ->assertSee('Body Wash')
        ->assertDontSee('Candle Scented');

    // Test category filter
    Livewire::test('shop.products')
        ->set('selectedCategory', $catHome->id)
        ->assertSee('Candle Scented')
        ->assertDontSee('Body Wash');

    tenancy()->end();
});

test('storefront product detail page selects variant and updates pricing', function () {
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);

    tenancy()->initialize($tenant);

    $category = Category::create(['name' => 'Skin', 'slug' => 'skin']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Luxury Cream',
        'slug' => 'luxury-cream',
        'price' => 1000,
        'status' => 'active',
        'featured' => false,
    ]);

    $v50 = ProductVariant::create([
        'product_id' => $product->id,
        'name' => 'Size',
        'value' => '50 ml',
        'sku' => 'LC-50',
        'price_modifier' => 0,
        'stock_on_hand' => 10,
        'stock_available' => 10,
    ]);

    $v100 = ProductVariant::create([
        'product_id' => $product->id,
        'name' => 'Size',
        'value' => '100 ml',
        'sku' => 'LC-100',
        'price_modifier' => 500, // + €5.00
        'stock_on_hand' => 2,
        'stock_available' => 2,
    ]);

    Livewire::test('shop.product-detail', ['product' => $product])
        ->assertSee('LC-50')
        ->assertSee('In Stock')
        ->assertSee('10,00') // DisplayPrice €10.00
        // Switch to 100ml variant
        ->call('selectVariant', $v100->id)
        ->assertSee('LC-100')
        ->assertSee('Only 2 left!')
        ->assertSee('15,00'); // DisplayPrice €15.00

    tenancy()->end();
});
