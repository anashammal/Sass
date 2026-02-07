<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client(['verify' => false, 'timeout' => 5]);
$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_20';

$actions = ['logout', 'stop', 'terminate', 'delete', 'remove', 'disconnect', 'restart'];
$prefixes = ['', 'session/', 'sessions/', 'api/'];

foreach ($prefixes as $prefix) {
    foreach ($actions as $action) {
        $url = $baseUrl . '/' . $prefix . $action;
        foreach (['GET', 'POST', 'DELETE'] as $method) {
            try {
                $opts = ['query' => ['session_id' => $sessionId, 'id' => $sessionId]];
                if ($method === 'POST') $opts['json'] = ['session_id' => $sessionId, 'id' => $sessionId];
                
                $r = $client->request($method, $url, $opts);
                echo "SUCCESS: $method $url -> " . $r->getStatusCode() . "\n";
                exit;
            } catch (Exception $e) {}
        }
    }
}
echo "Checked all, nothing worked.\n";
