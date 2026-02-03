<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate a request
$request = Illuminate\Support\Facades\Request::create('http://localhost/system/public/test', 'GET');
$app->instance('request', $request);

echo "Simulated URL: " . $request->url() . "\n";
echo "Base URL: " . $request->getBaseUrl() . "\n"; // Expect /system/public
echo "Root: " . $request->root() . "\n"; 

$generatedPath = $request->getBaseUrl() . '/storage/image.jpg';
echo "Proposed Relative Path: " . $generatedPath . "\n";

// Compare with asset()
echo "Asset() output: " . asset('storage/image.jpg') . "\n";
