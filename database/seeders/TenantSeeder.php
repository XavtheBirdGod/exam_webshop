<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        if (Tenant::where('id', 'shop-amsterdam')->exists()) {
            return;
        }

        $tenant = Tenant::create(['id' => 'shop-amsterdam']);
        $tenant->domains()->create(['domain' => 'amsterdam.localhost']);

        $tenant2 = Tenant::create(['id' => 'shop-paris']);
        $tenant2->domains()->create(['domain' => 'paris.localhost']);
    }
}
