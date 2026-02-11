<?php
// Debug script using Console Kernel to avoid HTTP middleware issues
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- Debugging Notification Logic (Console Mode) ---\n";

$sale = App\Models\Sale::latest()->first();
if (!$sale) {
    die("No sales found.\n");
}

echo "Last Sale ID: " . $sale->id . "\n";
echo "Total: " . $sale->total . "\n";

$store = App\Models\Store::find($sale->store_id);
if (!$store) {
    die("Store not found for Sale #" . $sale->id . "\n");
}

echo "Store Name: " . $store->name . "\n";
echo "Notify WhatsApp: " . ($store->notify_whatsapp ? 'Yes' : 'No') . "\n";
echo "Phone: " . $store->phone_number . "\n";
echo "Notify Sales: " . ($store->wa_notify_sales ? 'Yes' : 'No') . "\n";
echo "Min Sale Amount: " . ($store->wa_sales_min ?? 0) . "\n";
echo "Credit Only: " . ($store->wa_sales_credit_only ? 'Yes' : 'No') . "\n";

$netTotal = $sale->total;
$isCredit = ($sale->due > 0);
$sendInv = false;

if (!$sale->is_withdrawal && $store->wa_notify_sales) {
    if ($store->wa_sales_credit_only) {
        if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true;
    } else {
        if ($netTotal >= ($store->wa_sales_min ?? 0)) $sendInv = true;
        if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true;
    }
}

echo "Decision: " . ($sendInv ? 'SEND' : 'SKIP') . "\n";
