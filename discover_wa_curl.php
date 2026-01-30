<?php
$baseUrl = 'https://wa.tech-sys.online';
$sessionId = 'store_7';
$endpoints = [
    '/send-document',
    '/send-file',
    '/send-media',
    '/send-file-base64',
    '/message/send-media',
    '/send-message'
];

foreach ($endpoints as $ep) {
    $url = $baseUrl . $ep . '?session_id=' . $sessionId;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "GET $ep : Status $status\n";
    
    // Also try POST
    $ch = curl_init($baseUrl . $ep);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['session_id' => $sessionId]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "POST $ep : Status $status\n";
}
