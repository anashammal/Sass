<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\Store;

// 1. Determine Target Store
$store = Store::where('name', 'LIKE', '%الهنا%')
              ->orWhere('name', 'LIKE', '%Deluxe%')
              ->first();

if (!$store) {
    echo "Could not find target store (Al-Hana or Deluxe). Using Store ID 1 as fallback.\n";
    $storeId = 1;
} else {
    echo "Found Store: {$store->name} (ID: {$store->id})\n";
    $storeId = $store->id;
}

$baseUrl = config('services.whatsapp.url');
echo "Target WhatsApp Server URL: $baseUrl\n";

$sessionId = "store_{$storeId}";
echo "Target Session ID: $sessionId\n";

// Helper function to check status
function checkStatus($baseUrl, $sessionId) {
    try {
        $response = Http::withoutVerifying()->timeout(5)->get("$baseUrl/session-status", [
            'session_id' => $sessionId,
            'session' => $sessionId
        ]);
        echo "Status Check [$sessionId]: " . $response->status() . " | Connected: " . ($response->json('connected') ? 'YES' : 'NO') . "\n";
        return $response->json('connected');
    } catch (\Exception $e) {
        echo "Status Check Failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Helper to attempt logout
function tryLogout($baseUrl, $endpoint, $sessionId) {
    $url = "$baseUrl$endpoint";
    echo "--------------------------------------------------\n";
    echo "Attempting Logout via: $endpoint\n";
    
    $payload = [
        'session_id' => $sessionId,
        'session' => $sessionId,
        'id' => $sessionId
    ];
    
    try {
        $response = Http::withoutVerifying()->timeout(5)
            ->withBody(json_encode($payload), 'application/json')
            ->send('POST', $url);
            
        echo "Response Status: " . $response->status() . "\n";
        echo "Response Body: " . substr($response->body(), 0, 200) . "...\n";
        return $response->successful();
    } catch (\Exception $e) {
        echo "Request Failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// 2. Initial Status
echo "\n--- Initial Status ---\n";
$isConnected = checkStatus($baseUrl, $sessionId);

if (!$isConnected) {
    echo "WARNING: Session is NOT connected currently. Logout tests might be inconclusive.\n";
}

// 3. Test Endpoints
$endpoints = [
    '/session/terminate', // Common in some forks
    '/logout',            // Standard
    '/delete-session',    // Another common one
    '/session/logout',
    '/clients/logout'     // multi-device implementations
];

foreach ($endpoints as $ep) {
    tryLogout($baseUrl, $ep, $sessionId);
    sleep(1); // Brief pause
    
    // Check if it worked
    $stillConnected = checkStatus($baseUrl, $sessionId);
    if (!$stillConnected && $isConnected) {
        echo "SUCCESS: Disconnected successfully using $ep!\n";
        break; 
    }
}

echo "\n--- Probe Complete ---\n";
