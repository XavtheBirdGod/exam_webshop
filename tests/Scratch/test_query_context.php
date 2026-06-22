<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('shop-amsterdam');

$user = App\Models\User::where('email', 'amsterdam@rituals.com')->first();
var_dump($user->hasRoleInTenant('shop-amsterdam', 'shop_admin'));
