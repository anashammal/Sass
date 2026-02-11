<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting test...\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "Vendor loaded.\n";
    
    // We don't necessarily need the full App to test the Service if it doesn't use Facades/DB
    // ZatcaQrService uses TCPDF2DBarcode (manual require) and base64 functions.
    // It doesn't seem to use any Laravel specific facades (except maybe Str? No, I removed Str).
    
    // Let's try to load the service file manually if class not found
    require __DIR__ . '/../app/Services/ZatcaQrService.php';
    echo "Service file loaded.\n";

    $service = new \App\Services\ZatcaQrService();
    echo "Service instantiated.\n";

    $qr = $service->generate('My Store', '123456789012345', date('c'), '100.00', '15.00');
    echo "QR generated.\n";
    
    if (strpos($qr, 'data:image/png;base64') === 0) {
        echo "SUCCESS: QR Code generated. Length: " . strlen($qr) . "\n";
    } else {
        echo "FAILURE: Invalid output.\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
