<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Http;

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';
$paths = [
    '/logout/' . $sessionId,
    '/delete-session/' . $sessionId,
    '/session/delete/' . $sessionId,
    '/sessions/delete/' . $sessionId,
    '/terminate/' . $sessionId,
    '/logout',
    '/delete-session',
    '/session/terminate'
];

foreach ($paths as $path) {
    try {
        $url = $baseUrl . $path;
        $r = Http::withoutVerifying()->timeout(3)->post($url, ['session_id' => $sessionId]);
        echo "POST $path -> " . $r->status() . " Body: " . substr($r->body(), 0, 50) . "\n";
        
        $r = Http::withoutVerifying()->timeout(3)->delete($url, ['session_id' => $sessionId]);
        echo "DELETE $path -> " . $r->status() . " Body: " . substr($r->body(), 0, 50) . "\n";
    } catch (Exception $e) {
        echo "Error $path : " . $e->getMessage() . "\n";
    }
}
