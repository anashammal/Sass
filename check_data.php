<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Contact;

$users = User::with('store')->get();
foreach ($users as $u) {
    echo "User: {$u->email} | Store: " . ($u->store->name ?? 'N/A') . " (ID: " . ($u->store->id ?? 'N/A') . ")\n";
    if ($u->store) {
        $pCount = Product::where('store_id', $u->store->id)->count();
        $psCount = Product::where('store_id', $u->store->id)->whereHas('units', function($q){$q->where('is_sale',true);})->count();
        $cCount = Contact::where('store_id', $u->store->id)->count();
        echo "  - Products: $pCount ($psCount saleable)\n";
        echo "  - Contacts: $cCount\n";
    }
}
