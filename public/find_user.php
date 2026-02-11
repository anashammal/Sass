<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
$u = User::where('user_type', 'store_owner')->first();
if ($u) {
    echo "Found User ID: " . $u->id;
} else {
    echo "No Store Owner found.";
}
