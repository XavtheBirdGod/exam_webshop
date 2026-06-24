<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    
    Mockery::close();
});

test('user checkout journey integration test', function () {
    // 1. Setup Tenant
    $tenant = Tenant::create(['id' => 'test-shop']);
    $tenant->domains()->create(['domain' => 'test-shop.localhost']);

    tenancy()->initialize($tenant);
    Schema::disableForeignKeyConstraints();

    // 2. Setup Category, Product and Variant
    $category = Category::create(['name' => 'Skin Care', 'slug' => 'skin-care']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Rose Face Scrub',
        'slug' => 'rose-face-scrub',
        'price' => 1500, // €15.00
        'status' => 'active',
        'featured' => false,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'name' => 'Size',
        'value' => '100ml',
        'sku' => 'RFS-100',
        'price_modifier' => 500, // +€5.00 -> total €20.00
        'stock_on_hand' => 10,
        'stock_available' => 10,
    ]);

    // 3. Mock StripeClient and bind to container
    $mockStripe = Mockery::mock(\Stripe\StripeClient::class);
    $mockSessions = Mockery::mock();
    $mockSessions->shouldReceive('create')
        ->once()
        ->andReturn((object)[
            'id' => 'cs_test_12345',
            'url' => 'https://checkout.stripe.com/pay/cs_test_12345'
        ]);
    
    $mockCheckout = Mockery::mock();
    $mockCheckout->sessions = $mockSessions;

    $mockStripe->checkout = $mockCheckout;

    app()->instance(\Stripe\StripeClient::class, $mockStripe);

    // 4. Test Add To Cart using Livewire product-detail component
    Livewire::test('shop.product-detail', ['slug' => $product->slug])
        ->call('selectVariant', $variant->id)
        ->call('addToCart')
        ->assertHasNoErrors();

    // Verify cart contains the item
    $cartService = app(\App\Services\CartService::class);
    expect($cartService->getCount())->toBe(1);
    
    $details = $cartService->getCartDetails();
    expect($details)->toHaveCount(1);
    expect($details[0]['id'])->toBe($variant->id);
    expect($details[0]['price'])->toEqual(20.0);

    // 5. Test Checkout Component Validation & Submission
    $test = Livewire::test('shop.checkout.index')
        // Test validation first by calling processCheckout with empty values
        ->call('processCheckout')
        ->assertHasErrors(['email', 'first_name', 'last_name', 'address', 'city', 'zip_code'])
        // Fill details
        ->set('email', 'customer@example.com')
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('address', '123 Test Street')
        ->set('city', 'Amsterdam')
        ->set('zip_code', '1011AB')
        ->set('payment_method', 'stripe')
        ->call('processCheckout');

    $test->assertHasNoErrors()
        ->assertRedirect('https://checkout.stripe.com/pay/cs_test_12345');

    // 6. Assert Order generation in database
    $order = Order::first();
    expect($order)->not->toBeNull();
    expect($order->email)->toBe('customer@example.com');
    expect($order->status)->toBe('pending');
    expect($order->total_amount)->toBe(2000); // 20.00 EUR in cents
    expect($order->transaction_id)->toBe('cs_test_12345');
    
    $orderItems = $order->items;
    expect($orderItems)->toHaveCount(1);
    expect($orderItems->first()->name)->toBe('Rose Face Scrub (100ml)');
    expect($orderItems->first()->price)->toBe(2000);
    expect($orderItems->first()->quantity)->toBe(1);

    // Cart should be cleared after checkout submission
    expect($cartService->getCount())->toBe(0);

    Schema::enableForeignKeyConstraints();
    tenancy()->end();
});
