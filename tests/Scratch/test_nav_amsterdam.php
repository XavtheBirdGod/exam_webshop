<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('shop-amsterdam');

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
auth()->login($user);

$html = Illuminate\Support\Facades\Blade::render('<livewire:navigation />');

preg_match('/href="(.*?\/seller\/dashboard)"/', $html, $matches);
if (isset($matches[1])) {
    echo "URL is: " . $matches[1] . "\n";
} else {
    echo "NOT FOUND\n";
}
