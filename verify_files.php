<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$store = \App\Models\Store::find(7);
if (!$store) {
    echo "Store 7 not found\n";
    exit;
}

$pathsToCheck = [
    'logo' => $store->logo_path,
    'stamp' => $store->stamp_path,
    'sign' => $store->signature_path
];

foreach ($pathsToCheck as $type => $path) {
    if (!$path) continue;
    $fullPath = storage_path('app/public/' . $path);
    echo "$type Path: $path\n";
    echo "Full Path: $fullPath\n";
    echo "Exists: " . (file_exists($fullPath) ? "YES" : "NO") . "\n\n";
}
