<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client(['verify' => false, 'timeout' => 5]);
$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';

$endpoints = [
    '/logout',
    '/delete-session',
    '/session/terminate'
];

foreach ($endpoints as $ep) {
    try {
        $r = $client->request('POST', $baseUrl . $ep . '?session_id=' . $sessionId, [
            'json' => ['session_id' => $sessionId]
        ]);
        echo "SUCCESS: $ep -> " . $r->getStatusCode() . "\n";
    } catch (Exception $e) {
        echo "FAIL: $ep -> " . $e->getMessage() . "\n";
    }
}
