<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('shop-amsterdam');

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
$primaryTenant = $user->tenants->first(function ($tenant) {
    return in_array($tenant->pivot->role, ['shop_admin', 'shop_staff']);
});

var_dump($primaryTenant->id);
var_dump($primaryTenant->domains->first()->domain);
