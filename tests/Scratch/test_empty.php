<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $t = App\Models\Tenant::create(['id' => 'shop-empty']);
    $t->domains()->create(['domain' => 'empty.localhost']);
    $user = App\Models\User::first();
    $user->tenants()->syncWithPivotValues(['shop-empty'], ['role' => 'shop_admin']);
} catch (\Exception $e) {}

tenancy()->initialize('shop-empty');
auth()->login(App\Models\User::first());
$request = Illuminate\Http\Request::create('http://empty.localhost/seller/dashboard', 'GET');
$response = app()->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo $response->getContent();
}
