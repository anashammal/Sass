<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
echo "--- STORE TYPES DEBUG ---\n";
$stores = \App\Models\Store::all();
foreach ($stores as $store) {
    echo "ID: " . $store->id . " | Type: [" . $store->type . "]\n";
}
