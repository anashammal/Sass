<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

use App\Services\ZatcaQrService;

$service = new ZatcaQrService();
try {
    $qr = $service->generate('My Store', '123456789012345', date('c'), '100.00', '15.00');
    
    if (strpos($qr, 'data:image/png;base64') === 0) {
        echo "SUCCESS: QR Code generated. Length: " . strlen($qr);
        echo "\nSnippet: " . substr($qr, 0, 50) . "...";
        // Write to file to visually check if needed
        $data = base64_decode(substr($qr, strpos($qr, ',') + 1));
        file_put_contents(__DIR__ . '/test_qr.png', $data);
        echo "\nSaved to public/test_qr.png";
    } else {
        echo "FAILURE: Invalid output format. Got: " . substr($qr, 0, 100);
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage();
    echo "\nTrace: " . $e->getTraceAsString();
}
