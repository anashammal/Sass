<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Product;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

$user = User::find(8); // أنس حمال
Auth::login($user);

echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
echo "Store ID: " . ($user->store->id ?? 'N/A') . "\n";

$controller = app(App\Http\Controllers\StoreOwner\PosController::class);
$request = new Illuminate\Http\Request(['term' => '']);

echo "\n--- Product Search Results ---\n";
$resProd = $controller->searchProducts($request);
echo $resProd->getContent() . "\n";

echo "\n--- Customer Search Results ---\n";
$resCust = $controller->searchCustomers($request);
echo $resCust->getContent() . "\n";
