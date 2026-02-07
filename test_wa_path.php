<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';
$paths = [
    '/logout/' . $sessionId,
    '/delete-session/' . $sessionId,
    '/session/delete/' . $sessionId,
    '/sessions/delete/' . $sessionId,
    '/terminate/' . $sessionId,
];

foreach ($paths as $path) {
    try {
        $r = Http::withoutVerifying()->timeout(3)->post($baseUrl . $path);
        echo "POST $path -> " . $r->status() . "\n";
        $r = Http::withoutVerifying()->timeout(3)->delete($baseUrl . $path);
        echo "DELETE $path -> " . $r->status() . "\n";
    } catch (Exception $e) {
        echo "Error $path\n";
    }
}
