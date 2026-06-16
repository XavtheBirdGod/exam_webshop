<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        if (!Tenant::where('id', 'shop-amsterdam')->exists()) {
            $tenant = Tenant::create(['id' => 'shop-amsterdam']);
            $tenant->domains()->create(['domain' => 'amsterdam.localhost']);
        }

        if (!Tenant::where('id', 'shop-paris')->exists()) {
            $tenant2 = Tenant::create(['id' => 'shop-paris']);
            $tenant2->domains()->create(['domain' => 'paris.localhost']);
        }

        if (!Tenant::where('id', 'shop-london')->exists()) {
            $tenant3 = Tenant::create(['id' => 'shop-london']);
            $tenant3->domains()->create(['domain' => 'london.localhost']);
        }
    }
}
