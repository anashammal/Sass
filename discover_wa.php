<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_7';
$endpoints = [
    '/send-document',
    '/send-file-base64',
    '/send-media-base64',
    '/message/send-media',
    '/messages/send',
    '/send-message-media'
];

foreach ($endpoints as $ep) {
    try {
        $response = Http::withoutVerifying()->timeout(5)->get($baseUrl . $ep, ['session_id' => $sessionId]);
        echo "Endpoint $ep (GET): " . $response->status() . "\n";
    } catch (\Exception $e) {
        echo "Endpoint $ep (GET) Error: " . $e->getMessage() . "\n";
    }
}
