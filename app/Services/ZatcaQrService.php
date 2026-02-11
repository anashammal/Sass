<?php

namespace App\Services;

// Require the local library manually
if (!class_exists('TCPDF2DBarcode')) {
    require_once __DIR__ . '/Lib/TCPDF2DBarcode.php';
}

class ZatcaQrService
{
    /**
     * Generate ZATCA Compliant QR Code
     *
     * @param string $sellerName
     * @param string $vatNumber
     * @param string $timestamp
     * @param string $invoiceTotal
     * @param string $vatTotal
     * @return string Base64 encoded QR code image (Data URI)
     */
    public function generate($sellerName, $vatNumber, $timestamp, $invoiceTotal, $vatTotal)
    {
        // 1. Generate TLV String
        $tlvString = $this->getTlvString($sellerName, $vatNumber, $timestamp, $invoiceTotal, $vatTotal);

        // 2. Base64 Encode the TLV String (Content of the QR)
        $qrContent = base64_encode($tlvString);

        // 3. Generate QR Code Image using TCPDF2DBarcode
        // Type: QRCODE,M (Medium error correction)
        $barcodeObj = new \TCPDF2DBarcode($qrContent, 'QRCODE,M');
        
        // Generate PNG data (Width/Height 5px per module seems reasonable for print)
        // Returns raw string of PNG data
        $pngData = $barcodeObj->getBarcodePngData(4, 4, array(0,0,0));
        
        if ($pngData === false) {
             // Fallback if GD is missing: Return empty or error placeholder
             return null;
        }

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    /**
     * Build TLV (Tag-Length-Value) String
     */
    private function getTlvString($sellerName, $vatNumber, $timestamp, $invoiceTotal, $vatTotal)
    {
        $tlv  = $this->tlv(1, $sellerName);
        $tlv .= $this->tlv(2, $vatNumber);
        $tlv .= $this->tlv(3, $timestamp);
        $tlv .= $this->tlv(4, $invoiceTotal);
        $tlv .= $this->tlv(5, $vatTotal);
        return $tlv;
    }

    /**
     * Construct a single TLV tag
     */
    private function tlv($tag, $value)
    {
        return pack('H*', sprintf('%02X', $tag)) . 
               pack('H*', sprintf('%02X', strlen($value))) . 
               $value;
    }
}
