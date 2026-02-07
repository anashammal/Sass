<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client(['verify' => false, 'timeout' => 5]);
$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';

$patterns = [
    '/logout/' . $sessionId,
    '/delete-session/' . $sessionId,
    '/session/delete/' . $sessionId,
    '/sessions/delete/' . $sessionId,
    '/terminate/' . $sessionId,
    '/session/' . $sessionId . '/logout',
    '/sessions/' . $sessionId . '/logout',
];

foreach ($patterns as $p) {
    foreach (['POST', 'DELETE'] as $method) {
        try {
            $r = $client->request($method, $baseUrl . $p);
            echo "SUCCESS: $method $p -> " . $r->getStatusCode() . "\n";
            exit;
        } catch (Exception $e) {}
    }
}
echo "Nothing worked.\n";
