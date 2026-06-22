<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
$primaryTenant = $user->tenants->first();
var_dump($primaryTenant->domains->count());
