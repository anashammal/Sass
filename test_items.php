<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Purchase::find(129);
if(!$p) {
    echo "Purchase not found\n";
    exit;
}
echo "Total items: " . $p->items()->count() . "\n";
foreach($p->items as $i) {
    echo "Item ID: {$i->id}, Product ID: {$i->product_id}\n";
}
