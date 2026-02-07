<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20'; // Use one that is authenticated
$methods = ['POST', 'GET', 'DELETE'];
$paths = [
    '/logout',
    '/session/logout',
    '/sessions/logout',
    '/terminate',
    '/session/terminate',
    '/sessions/terminate',
    '/delete-session',
    '/session/delete',
    '/sessions/delete',
    '/stop',
    '/session/stop',
    '/sessions/stop',
    '/remove-session',
    '/session/remove',
    '/sessions/remove',
    '/unlink',
    '/session/unlink'
];

foreach ($paths as $path) {
    foreach ($methods as $method) {
        try {
            if ($method == 'POST') {
                $r = Http::withoutVerifying()->timeout(3)->post($baseUrl . $path, ['session_id' => $sessionId]);
            } elseif ($method == 'GET') {
                $r = Http::withoutVerifying()->timeout(3)->get($baseUrl . $path, ['session_id' => $sessionId]);
            } elseif ($method == 'DELETE') {
                $r = Http::withoutVerifying()->timeout(3)->delete($baseUrl . $path, ['session_id' => $sessionId]);
            }
            if ($r->status() != 404) {
                echo "FOUND: $method $path -> Status " . $r->status() . " Body: " . substr($r->body(), 0, 100) . "\n";
            } else {
                // echo "404: $method $path\n";
            }
        } catch (Exception $e) {
            // echo "Error: $method $path\n";
        }
    }
}
echo "Done brute forcing.\n";
