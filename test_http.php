<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
auth()->login($user);

// Create request to paris.localhost
$request = Illuminate\Http\Request::create('http://paris.localhost/', 'GET');
$request->setLaravelSession(session());

$response = $kernel->handle($request);
$content = $response->getContent();

if (strpos($content, 'Vendor Dashboard') !== false) {
    echo "SUCCESS: Vendor Dashboard found in HTML!\n";
} else {
    echo "FAILED: Vendor Dashboard NOT found in HTML!\n";
    // echo $content;
}
