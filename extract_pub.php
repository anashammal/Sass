<?php
$privKey = file_get_contents('temp_dkim.key');
$res = openssl_pkey_get_private($privKey);
if (!$res) {
    die("Failed to load private key\n");
}
$details = openssl_pkey_get_details($res);
$pubKey = $details['key'];

// Extract the base64 part between BEGIN and END tags
if (preg_match('/-----BEGIN PUBLIC KEY-----(.*)-----END PUBLIC KEY-----/s', $pubKey, $matches)) {
    $cleanKey = str_replace(["\n", "\r"], '', trim($matches[1]));
    echo $cleanKey;
} else {
    echo "Failed to extract clean key\n";
}
