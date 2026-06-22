<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
auth()->login($user);

// We will use blade to render the livewire component
$html = Illuminate\Support\Facades\Blade::render('<livewire:navigation />');

preg_match('/href="(.*?\/seller\/dashboard)"/', $html, $matches);
if (isset($matches[1])) {
    echo "Dashboard URL is: " . $matches[1] . "\n";
} else {
    echo "Dashboard URL NOT FOUND in HTML.\n";
}
