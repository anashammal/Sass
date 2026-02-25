<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$media = \Illuminate\Support\Facades\DB::table('media')
            ->where('id', 129)
            ->first();

echo "\n--- MEDIA 129 ---\n";
echo json_encode($media, JSON_PRETTY_PRINT);
echo "\n";
