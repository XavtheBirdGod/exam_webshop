<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('http://paris.localhost/seller/dashboard', 'GET');
$response = app()->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
// echo $response->getContent();
