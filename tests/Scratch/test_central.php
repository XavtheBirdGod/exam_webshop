<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); // Ensures config is loaded

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('http://127.0.0.1:8000/seller/dashboard', 'GET');
$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo substr($response->getContent(), 0, 500); // Print first 500 chars to see error
} else {
    echo "SUCCESS\n";
}
