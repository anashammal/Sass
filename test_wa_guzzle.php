<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client(['verify' => false, 'timeout' => 5]);

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';

$endpoints = [
    ['method' => 'GET', 'url' => '/session-status', 'query' => ['session_id' => $sessionId]],
    ['method' => 'POST', 'url' => '/logout', 'json' => ['session_id' => $sessionId]],
    ['method' => 'POST', 'url' => '/delete-session', 'json' => ['session_id' => $sessionId]],
    ['method' => 'POST', 'url' => '/session/terminate', 'json' => ['session_id' => $sessionId]],
    ['method' => 'DELETE', 'url' => '/sessions/' . $sessionId, 'json' => []],
    ['method' => 'POST', 'url' => '/sessions/delete/' . $sessionId, 'json' => []],
];

foreach ($endpoints as $ep) {
    try {
        $options = [];
        if (isset($ep['query'])) $options['query'] = $ep['query'];
        if (isset($ep['json'])) $options['json'] = $ep['json'];
        
        $response = $client->request($ep['method'], $baseUrl . $ep['url'], $options);
        echo "SUCCESS: {$ep['method']} {$ep['url']} -> {$response->getStatusCode()}\n";
        echo "Body: " . substr($response->getBody(), 0, 100) . "\n\n";
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "FAILED: {$ep['method']} {$ep['url']} -> " . ($e->hasResponse() ? $e->getResponse()->getStatusCode() : $e->getMessage()) . "\n";
        if ($e->hasResponse()) {
            echo "Body: " . substr($e->getResponse()->getBody(), 0, 100) . "\n";
        }
        echo "\n";
    }
}
