<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductUnit;
$u = ProductUnit::first();
if ($u) {
    print_r($u->getAttributes());
} else {
    echo "No product units found.\n";
}
