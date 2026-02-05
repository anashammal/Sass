<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$stores = \App\Models\Store::where(function($q) {
    $q->whereNotNull('logo_path')
      ->orWhereNotNull('stamp_path')
      ->orWhereNotNull('signature_path');
})->take(5)->get();

foreach ($stores as $s) {
    echo "ID: " . $s->id . " | Logo: " . $s->logo_path . " | Stamp: " . $s->stamp_path . " | Sign: " . $s->signature_path . "\n";
}
