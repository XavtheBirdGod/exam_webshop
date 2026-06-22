<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); // Ensures config is loaded

$request = Illuminate\Http\Request::create('http://amsterdam.localhost/', 'GET');
$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->isRedirection()) {
    echo "Redirect Location: " . $response->headers->get('Location') . "\n";
}
