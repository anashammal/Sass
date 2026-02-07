<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client(['verify' => false, 'timeout' => 5]);

$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';

$variants = [
    ['method' => 'POST', 'url' => '/sessions/delete'],
    ['method' => 'POST', 'url' => '/session/delete'],
    ['method' => 'POST', 'url' => '/delete-session'],
    ['method' => 'POST', 'url' => '/remove-session'],
    ['method' => 'DELETE', 'url' => '/sessions'],
    ['method' => 'DELETE', 'url' => '/session'],
];

foreach ($variants as $v) {
    echo "Testing {$v['method']} {$v['url']} with body ID: $sessionId\n";
    try {
        $r = $client->request($v['method'], $baseUrl . $v['url'], [
            'json' => ['session_id' => $sessionId, 'session' => $sessionId, 'id' => $sessionId]
        ]);
        echo "FOUND!! Status: " . $r->getStatusCode() . " Body: " . $r->getBody() . "\n";
    } catch (Exception $e) {
        echo "Fail: " . $e->getMessage() . "\n";
    }
}
