<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

function testLogout($endpoint, $method = 'POST', $storeId = 7) {
    $baseUrl = 'https://wa.tech-sys.online';
    $sessionId = "store_{$storeId}"; // Use store ID 7 as seen in previous debug script 
    
    echo "Testing $method $baseUrl$endpoint for session $sessionId...\n";
    
    $payload = [
        'session_id' => $sessionId,
        'session' => $sessionId,
        'id' => $sessionId
    ];
    
    try {
        $jsonPayload = json_encode($payload);
        
        $response = Http::withoutVerifying()->timeout(10)
            ->withBody($jsonPayload, 'application/json')
            ->send($method, $baseUrl . $endpoint);
            
        echo "Status: " . $response->status() . "\n";
        echo "Body: " . $response->body() . "\n";
        echo "----------------------------------------\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "----------------------------------------\n";
    }
}

// Check status first
echo "Checking status first...\n";
testLogout('/session-status', 'GET', 23); // testing specifically for the store in screenshot if possible? 
// The screenshot doesn't show store ID clearly, but user said "store owner" settings.
// I'll try a generic one or rely on the code to pick right one if I was running in context. 
// Use a safe guess or just probe endpoints.

// Probing Endpoints
$endpoints = [
    '/logout',
    '/delete-session',
    '/session/terminate',
    '/session/logout',
    '/clients/logout',
    '/session/delete'
];

foreach ($endpoints as $ep) {
    testLogout($ep, 'POST', 23); // 23 is a guess, but the endpoint existence is what matters mostly for 404 vs 200/500
    // If 404, endpoint wrong. 
    // If 200/true, correct.
}

// Try DELETE method
foreach ($endpoints as $ep) {
    testLogout($ep, 'DELETE', 23);
}
