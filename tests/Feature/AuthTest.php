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
