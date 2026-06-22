<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::with('tenants')->get();
foreach ($users as $u) {
    echo $u->email . ': ';
    foreach ($u->tenants as $t) {
        echo $t->id . ' (' . $t->pivot->role . ') ';
    }
    echo "\n";
}
