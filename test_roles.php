<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'amsterdam@rituals.com')->first();
var_dump($user->hasRoleInTenant('shop-amsterdam', 'shop_admin'));
var_dump($user->hasRoleInTenant('shop-amsterdam', 'shop_staff'));

$paris = App\Models\User::where('email', 'paris@rituals.com')->first();
var_dump($paris->hasRoleInTenant('shop-paris', 'shop_admin'));
