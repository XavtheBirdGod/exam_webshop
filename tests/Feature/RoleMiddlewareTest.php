<?php

use App\Models\User;
use App\Models\Tenant;
use App\Enums\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    foreach (glob(database_path('tenant*')) as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    // Setup test routes
    Route::middleware(['web', 'platform-admin'])->get('/test-platform-admin', function () {
        return 'platform-admin-success';
    });

    Route::middleware(['web', 'tenant-role:shop_admin'])->get('/test-tenant-admin', function () {
        return 'tenant-admin-success';
    });
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

test('platform-admin middleware allows platform admins', function () {
    $admin = User::factory()->create(['role' => Role::PLATFORM_ADMIN]);

    actingAs($admin)
        ->get('/test-platform-admin')
        ->assertStatus(200)
        ->assertSee('platform-admin-success');
});

test('platform-admin middleware denies non-platform admins', function () {
    $customer = User::factory()->create(['role' => Role::CUSTOMER]);

    actingAs($customer)
        ->get('/test-platform-admin')
        ->assertStatus(403);
});

test('tenant-role middleware allows tenant admins in tenant context', function () {
    // Create tenant
    $tenant = Tenant::create(['id' => 'test-tenant']);
    
    // Create user
    $user = User::factory()->create(['role' => Role::CUSTOMER]);
    
    // Link user to tenant as shop_admin
    $user->tenants()->attach($tenant->id, ['role' => Role::SHOP_ADMIN]);

    // Initialize tenancy
    tenancy()->initialize($tenant);

    actingAs($user)
        ->get('/test-tenant-admin')
        ->assertStatus(200)
        ->assertSee('tenant-admin-success');
        
    tenancy()->end();
});

test('tenant-role middleware denies unauthorized users in tenant context', function () {
    $tenant = Tenant::create(['id' => 'test-tenant-2']);
    $user = User::factory()->create(['role' => Role::CUSTOMER]);

    // Initialize tenancy
    tenancy()->initialize($tenant);

    actingAs($user)
        ->get('/test-tenant-admin')
        ->assertStatus(403);
        
    tenancy()->end();
});
