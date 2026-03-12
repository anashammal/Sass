<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

// Try to find a store with name "مطعم الهنا"
$store = \App\Models\Store::where('name', 'like', '%الهنا%')->first();
if ($store) {
    echo "Found Store: " . $store->name . " | ID: " . $store->id . "\n";
    echo "Base Currency: " . $store->baseCurrency->code . " (ID: " . $store->base_currency_id . ")\n";
    echo "Accepted Currencies:\n";
    foreach ($store->acceptedCurrencies as $cur) {
        echo "  - " . $cur->code . " (ID: " . $cur->id . ")\n";
    }
} else {
    echo "Store 'Al-Hana' not found.\n";
}
