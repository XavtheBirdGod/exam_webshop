<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoginForm extends Form
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function authenticate(): void
    {
        $this->validate();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if ($user && tenant('id')) {
            if (in_array($user->role, [\App\Enums\Role::SHOP_ADMIN, \App\Enums\Role::SHOP_STAFF])) {
                $hasAccess = $user->tenants()->where('tenant_id', tenant('id'))->exists();
                if (!$hasAccess) {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'form.email' => 'You do not have access to this store.',
                    ]);
                }
            }
        }
    }
}
