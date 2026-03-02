<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\StoreOwner\PosController;
use Illuminate\Support\Facades\Auth;

$userId = 23; 
$user = User::find($userId);
if (!$user) die("User $userId not found\n");
Auth::login($user);

$controller = app(PosController::class);

echo "--- Testing Customers ---\n";
$reqC = new Request(['term' => '']);
$resC = $controller->searchCustomers($reqC);
echo $resC->getContent() . "\n";

echo "--- Testing Products ---\n";
$reqP = new Request(['term' => '']);
$resP = $controller->searchProducts($reqP);
echo $resP->getContent() . "\n";
