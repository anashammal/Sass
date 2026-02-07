<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Route: " . route('store.pos.search-products') . "\n";
echo "URL: " . url('/') . "\n";
echo "Config APP_URL: " . config('app.url') . "\n";
