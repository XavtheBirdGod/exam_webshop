<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); // Ensures config is loaded

$user = App\Models\User::where('email', 'amsterdam@rituals.com')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('http://amsterdam.localhost/seller/dashboard', 'GET');
$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo $response->getContent();
} else {
    echo "SUCCESS\n";
}
