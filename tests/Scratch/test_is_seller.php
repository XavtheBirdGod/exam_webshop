<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

auth()->login(App\Models\User::where('email', 'paris@rituals.com')->first());
$user = auth()->user();
$access = false;

// If on central domain, check if they have access to ANY tenant
foreach ($user->tenants as $tenant) {
    if (in_array($tenant->pivot->role, ['shop_admin', 'shop_staff'])) {
        $access = true;
    }
}
var_dump($access);
