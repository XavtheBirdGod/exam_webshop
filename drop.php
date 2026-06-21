<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dbs = DB::select("SHOW DATABASES LIKE 'tenant%'");
foreach($dbs as $db) {
    $name = array_values((array)$db)[0];
    DB::statement("DROP DATABASE `$name`");
    echo "Dropped $name\n";
}
