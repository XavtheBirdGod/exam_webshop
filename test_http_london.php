<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); // Ensures config is loaded

$user = App\Models\User::where('email', 'london@rituals.com')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('http://london.localhost/', 'GET');
$response = $kernel->handle($request);

if ($response->getStatusCode() === 200) {
    if (strpos($response->getContent(), 'Vendor Dashboard') !== false) {
        echo "SUCCESS: Vendor Dashboard found!\n";
    } else {
        echo "FAILED: Vendor Dashboard NOT found. Status code 200.\n";
    }
} else {
    echo "FAILED: Status Code " . $response->getStatusCode() . "\n";
    echo $response->getContent();
}
