<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$store = \App\Models\Store::find(20);
echo "Store: " . $store->name . " (ID: 20)\n";
echo "Base Currency ID: " . $store->base_currency_id . "\n";
echo "Base Currency Code: " . ($store->baseCurrency->code ?? 'N/A') . "\n";

echo "\nAccepted Currencies:\n";
foreach($store->acceptedCurrencies as $cur) {
    echo "- ID: {$cur->id}, Code: {$cur->code}\n";
}
