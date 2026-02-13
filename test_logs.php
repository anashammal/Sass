<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Product;
use App\Models\User;
use App\Models\InventoryActionLog;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

$product = Product::find(14); // HYPO
$oldStock = $product->current_stock;
$newStock = 20;

$product->current_stock = $newStock;
$product->save();

$log = InventoryActionLog::create([
    'store_id' => $product->store_id,
    'product_id' => $product->id,
    'user_id' => $user->id,
    'action' => 'manual_adjustment',
    'quantity' => abs($newStock - $oldStock),
    'old_quantity' => $oldStock,
    'new_quantity' => $newStock,
    'reason' => 'Test Log from Script',
]);

if ($log->id) {
    echo "SUCCESS: Log created with ID: " . $log->id . "\n";
    echo "Old Stock: " . $oldStock . " | New Stock: " . $newStock . "\n";
} else {
    echo "FAILED: Could not create log.\n";
}
