<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// This script will be run via php artisan tinker or similar
function testWA($endpoint, $payload, $method = 'POST') {
    $baseUrl = 'https://wa.tech-sys.online';
    echo "Testing $endpoint with method $method...\n";
    try {
        if ($method == 'POST') {
            $response = Http::withoutVerifying()->timeout(10)->post($baseUrl . $endpoint, $payload);
        } else {
            $response = Http::withoutVerifying()->timeout(10)->get($baseUrl . $endpoint, $payload);
        }
        echo "Status: " . $response->status() . "\n";
        echo "Body: " . $response->body() . "\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

$sessionId = 'store_7';
$phone = '966555555555'; // Test number
$smallFile = base64_encode('test file content');
$dataUri = 'data:application/pdf;base64,' . $smallFile;

// Trial 1: JSON Standard
testWA('/send-message', [
    'session_id' => $sessionId,
    'phone' => $phone,
    'message' => 'Test JSON Base64',
    'media' => $dataUri
]);

// Trial 2: JSON with nested media
testWA('/send-message', [
    'session_id' => $sessionId,
    'phone' => $phone,
    'message' => 'Test JSON Nested',
    'media' => ['url' => $dataUri, 'filename' => 'test.pdf']
]);

// Trial 3: send-media endpoint
testWA('/send-media', [
    'session_id' => $sessionId,
    'phone' => $phone,
    'caption' => 'Test send-media',
    'media' => $dataUri
]);
