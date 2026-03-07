<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 128; // The invoice ID from the screenshot
$purchase = \App\Models\Purchase::with(['items.product', 'items.unit', 'supplier'])->find($id);
if(!$purchase) {
    echo "Purchase 128 not found\n";
    exit;
}

$itemsData = [];
foreach($purchase->items as $item) {
    if(!$item->product) continue;
    
    $img = $item->product->image_url;
    if($item->unit && $item->unit->hasMedia('unit_images')) {
        $img = $item->unit->image;
    }

    $units = $item->product->units->map(function($u) {
            return [
            'id' => $u->id,
            'unit_name' => $u->unit_name,
            'is_base_unit' => $u->is_base_unit,
            'conversion_factor' => $u->conversion_factor,
            'is_purchase' => $u->is_purchase,
            'barcode' => $u->barcode, 
            'cost_price' => $u->cost_price,
            'purchase_price' => $u->purchase_price,
            'selling_price' => $u->selling_price,
            'profit_percent' => $u->profit_percent,
            ];
    });

    $itemsData[] = [
        'id' => $item->id,
        'product_id' => $item->product_id,
        'product_unit_id' => $item->product_unit_id,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
        'total_cost' => $item->total_cost,
        'expiry_date' => $item->expiry_date,
        'alert_days' => $item->alert_days,
        'product' => [
            'id' => $item->product->id,
            'name' => $item->product->name,
            'image_url' => $img,
            'scanned_unit_id' => $item->product_unit_id,
            'units' => $units
        ]
    ];
}

echo json_encode($itemsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
