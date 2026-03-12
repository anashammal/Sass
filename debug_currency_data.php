<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Currency;

$storeId = 7; // Assuming from previous context or common ID

echo "Auditing Products for Store ID: $storeId\n";

$products = Product::where('store_id', $storeId)
    ->whereIn('product_type', ['meal', 'ingredient', 'compound', 'standard'])
    ->with('baseUnit')
    ->get();

echo "Total Products Found: " . $products->count() . "\n";

foreach ($products as $p) {
    $bu = $p->baseUnit;
    echo "Product: {$p->id} - {$p->name} | Type: {$p->product_type}\n";
    if ($bu) {
        $currId = $bu->sell_price_currency_id;
        $curr = Currency::find($currId);
        $currCode = $curr ? $curr->code : 'NULL/Unknown';
        echo "  - Base Unit ID: {$bu->id} | sell_price_currency_id: " . ($currId ?? 'NULL') . " ($currCode)\n";
    } else {
        echo "  - NO BASE UNIT FOUND!\n";
    }
    echo "---------------------------------\n";
}

// Also check all units for ONE specific product from the screenshot "بطاطا مقلية"
$potato = Product::where('store_id', $storeId)->where('name_ar', 'like', '%بطاطا مقلية%')->first();
if ($potato) {
    echo "\nDetailed Check for: " . $potato->name_ar . "\n";
    $units = ProductUnit::where('product_id', $potato->id)->get();
    foreach ($units as $u) {
        echo "  Unit: {$u->unit_name} | Base: " . ($u->is_base_unit ? 'YES' : 'NO') . " | Currency ID: " . ($u->sell_price_currency_id ?? 'NULL') . "\n";
    }
}
