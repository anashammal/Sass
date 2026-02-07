<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'system';
$tests = [
    ['GET', '/session-status'],
    ['POST', '/send-message'],
    ['POST', '/logout'],
    ['POST', '/terminate'],
    ['DELETE', '/sessions/system'],
    ['GET', '/logout']
];

foreach ($tests as $test) {
    list($method, $path) = $test;
    try {
        if ($method == 'POST') {
            $r = Http::withoutVerifying()->timeout(3)->post($baseUrl . $path, ['session_id' => $sessionId, 'phone' => '123', 'message' => 'test']);
        } elseif ($method == 'GET') {
            $r = Http::withoutVerifying()->timeout(3)->get($baseUrl . $path, ['session_id' => $sessionId]);
        } elseif ($method == 'DELETE') {
            $r = Http::withoutVerifying()->timeout(3)->delete($baseUrl . $path, ['session_id' => $sessionId]);
        }
        echo "Test: $method $path -> Status " . $r->status() . "\n";
    } catch (Exception $e) {
        echo "Error: $method $path -> " . $e->getMessage() . "\n";
    }
}
