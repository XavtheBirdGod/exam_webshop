<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'paris@rituals.com')->first();
auth()->login($user);

// We will use blade to render the livewire component
$html = Illuminate\Support\Facades\Blade::render('<livewire:navigation />');

if (strpos($html, 'Vendor Dashboard') !== false) {
    echo "SUCCESS: Vendor Dashboard found in Livewire render!\n";
} else {
    echo "FAILED: Vendor Dashboard NOT found in Livewire render.\n";
    // We will print the DEBUG line we just added
    preg_match('/DEBUG: (.*?)<\/p>/', $html, $matches);
    if (isset($matches[1])) {
        echo "DEBUG INFO: " . $matches[1] . "\n";
    }
}
