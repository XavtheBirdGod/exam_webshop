<?php

use App\Models\User;
use App\Enums\Role;
use Livewire\Livewire;

test('users can register on the central site', function () {
    Livewire::test('auth.register')
        ->set('form.name', 'John Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertRedirect('/');

    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
    expect(auth()->check())->toBeTrue();
});

test('users can log in on the central site', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('password123'),
        'role' => Role::CUSTOMER,
    ]);

    Livewire::test('auth.login')
        ->set('form.email', 'jane@example.com')
        ->set('form.password', 'password123')
        ->call('login')
        ->assertRedirect('/');

    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->id)->toBe($user->id);
});

test('shop admin of one tenant cannot log in to another tenant store', function () {
    $amsterdamAdmin = User::factory()->create([
        'email' => 'amsterdam_test@rituals.com',
        'password' => bcrypt('password123'),
        'role' => Role::SHOP_ADMIN,
    ]);
    
    $tenantAmsterdam = \App\Models\Tenant::create(['id' => 'shop-amsterdam-test']);
    $tenantParis = \App\Models\Tenant::create(['id' => 'shop-paris-test']);
    
    $amsterdamAdmin->tenants()->attach($tenantAmsterdam->id, ['role' => Role::SHOP_ADMIN->value]);
    
    // Initialize tenancy for Paris
    tenancy()->initialize($tenantParis);
    
    Livewire::test('auth.login')
        ->set('form.email', 'amsterdam_test@rituals.com')
        ->set('form.password', 'password123')
        ->call('login')
        ->assertHasErrors(['form.email']);
        
    expect(auth()->check())->toBeFalse();
    
    tenancy()->end();
    
    $tenantAmsterdam->delete();
    $tenantParis->delete();
});
