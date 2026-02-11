<?php

use App\Models\Sale;
use App\Models\Store;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Debugging Notification Logic ---\n";

$sale = Sale::latest()->first();
if (!$sale) {
    die("No sales found.\n");
}

echo "Last Sale ID: " . $sale->id . "\n";
echo "Specific Store ID: " . $sale->store_id . "\n";
echo "Total: " . $sale->total . "\n";
echo "Is Withdrawal: " . ($sale->is_withdrawal ? 'Yes' : 'No') . "\n";

$store = Store::find($sale->store_id);
echo "Store Name: " . $store->name . "\n";
echo "Notify WhatsApp: " . ($store->notify_whatsapp ? 'Yes' : 'No') . "\n";
echo "Phone: " . $store->phone_number . "\n";
echo "Notify Sales: " . ($store->wa_notify_sales ? 'Yes' : 'No') . "\n";
echo "Min Sale Amount: " . ($store->wa_sales_min ?? 0) . "\n";
echo "Credit Only: " . ($store->wa_sales_credit_only ? 'Yes' : 'No') . "\n";

// Simulate logic
$netTotal = $sale->total;
$isCredit = ($sale->due > 0);
$sendInv = false;

if (!$sale->is_withdrawal && $store->wa_notify_sales) {
    if ($store->wa_sales_credit_only) {
        echo "Check: Credit Only Mode.\n";
        if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true;
    } else {
        echo "Check: Normal Mode.\n";
        if ($netTotal >= ($store->wa_sales_min ?? 0)) {
            $sendInv = true;
            echo "Result: NetTotal ($netTotal) >= Min (" . ($store->wa_sales_min ?? 0) . ")\n";
        } else {
            echo "Result: NetTotal ($netTotal) < Min (" . ($store->wa_sales_min ?? 0) . ")\n";
        }
        
        if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true;
    }
}

echo "Will Send? " . ($sendInv ? 'YES' : 'NO') . "\n";
echo "------------------------------------\n";
