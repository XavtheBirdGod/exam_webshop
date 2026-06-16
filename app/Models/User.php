<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Enums\Role;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->connection = config('tenancy.database.central_connection') ?: config('database.default');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users', 'user_id', 'tenant_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isPlatformAdmin(): bool
    {
        return $this->role === Role::PLATFORM_ADMIN;
    }

    public function hasRoleInTenant($tenantId, $role): bool
    {
        return $this->tenants()
            ->where('tenant_id', $tenantId)
            ->wherePivot('role', $role)
            ->exists();
    }
}
