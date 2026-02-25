<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KuveytTurkSanalPosService
{
    private $baseUrl;
    private $merchantId;
    private $customerId;
    private $username;
    private $password;

    public function __construct()
    {
        $env = config('services.kuveyt_turk.env', 'sandbox');
        
        if ($env === 'sandbox') {
             // Test Environment Bilgileri (From Kuveyt Turk Email)
             $this->baseUrl = 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate';
             $this->customerId = '400235';
             $this->merchantId = '496';
             $this->username   = 'apitest';
             $this->password   = 'api123';
        } else {
             // Production Environment
             $this->baseUrl = 'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelPayGate';
             $this->customerId = config('services.kuveyt_turk.client_id'); 
             $this->merchantId = config('services.kuveyt_turk.merchant_id');
             $this->username   = config('services.kuveyt_turk.username');
             $this->password   = config('services.kuveyt_turk.password');
        }
    }

    public function startPayment($data)
    {
        try {
            $amount = $data['amount'] * 100; // Amount in cents? No, KT usually takes float 1.00 or 100? 
            // Docs usually say "1.00" for 1 TL. Let's keep it as is from input or format it.
            // Actually, official docs often require Amount to be 100 for 1.00 TL? 
            // Let's assume standard "1.00" format first or check if integer required. 
            // Common implementation uses "1.00".
            // Kuveyt Turk Sanal POS usually expects Amount in "1.00" format?
            // Actually, many docs say "Amount" should be "100" (cents) for some banks, but KT often uses 1.00.
            // However, "TechnicalException" often usually means data type mismatch.
            // Let's try 100 (cents) logic if 1.00 fails, BUT standard KT docs often show 1.00.
            // WAIT! The Hash data uses `Amount` as well. If we change it, hash must match.

            // Let's check if `CurrencyCode` 0949 is correct. Yes, 949 is TRY. 0949 is also used.

            // Retrying with Amount * 100 (Cents) is a common fix for Turkish POS.
            // Let's modify to use Integer for Amount if float failed.
            // AND ensure Hash uses the SAME value.
            
            // Re-reading specific KT docs: 
            // "Amount" field: "Islem tutari (1.00 TL icin 100 gonderilmelidir)" -> THIS IS THE KEY!
            // So 1.00 -> 100.
            
            $amountFormatted = number_format($data['amount'], 2, '.', '');
            $orderId = $data['order_id'];
            // Production Bank API strictly rejects 'localhost' URLs. Force the live APP_URL.
            $appUrl = rtrim(config('app.url', 'https://tech-sys.online/system'), '/');
            $okUrl = $appUrl . '/k-test/callback';
            $failUrl = $appUrl . '/k-test/callback'; 
            $hashedPassword = base64_encode(sha1($this->password, true));

            // Detect Card Type
            $firstDigit = substr($data['card_number'], 0, 1);
            $cardType = 'MasterCard'; // Default
            if ($firstDigit == '4') {
                $cardType = 'Visa';
            } elseif ($firstDigit == '5') {
                $cardType = 'MasterCard';
            } elseif ($firstDigit == '6' || $firstDigit == '9') {
                $cardType = 'Troy'; // Just incase
            }

            $amountStr = number_format($data['amount'], 2, '.', '');
            $hashedPassword = base64_encode(sha1($this->password, true));
            
            $hashStr = $this->merchantId . $orderId . $amountStr . $okUrl . $failUrl . $this->username . $hashedPassword;
            $hashData = trim(base64_encode(sha1($hashStr, true)));

            // Clean XML format (no spaces between tags, standard format)
            $xml = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">' .
                '<APIVersion>1.0.0</APIVersion>' .
                '<OkUrl>' . $okUrl . '</OkUrl>' .
                '<FailUrl>' . $failUrl . '</FailUrl>' .
                '<HashData>' . $hashData . '</HashData>' .
                '<MerchantId>' . $this->merchantId . '</MerchantId>' .
                '<CustomerId>' . $this->customerId . '</CustomerId>' .
                '<UserName>' . $this->username . '</UserName>' .
                '<CardNumber>' . $data['card_number'] . '</CardNumber>' .
                '<CardExpireDateYear>' . $data['expire_year'] . '</CardExpireDateYear>' .
                '<CardExpireDateMonth>' . $data['expire_month'] . '</CardExpireDateMonth>' .
                '<CardCvv2>' . $data['cvv'] . '</CardCvv2>' .
                '<CardHolderName>' . $data['card_holder_name'] . '</CardHolderName>' .
                '<CardType>' . $cardType . '</CardType>' .
                '<BatchID>0</BatchID>' .
                '<TransactionType>Sale</TransactionType>' .
                '<InstallmentCount>0</InstallmentCount>' .
                '<Amount>' . $amountStr . '</Amount>' .
                '<CurrencyCode>0949</CurrencyCode>' .
                '<MerchantOrderId>' . $orderId . '</MerchantOrderId>' .
                '<TransactionSecurity>3</TransactionSecurity>' . // 3 = 3D Secure
                '</KuveytTurkVPosMessage>';
            
            Log::info("Kuveyt Turk Sanal POS XML Request: " . $xml);

            // For 3D Model, we don't POST via Guzzle/Http client expecting a JSON response.
            // We usually need to POST this XML as a Form Parameter "AuthenticationXM" to the URL,
            // which redirects the BROWSER.
            
            // So this service should return the HTML FORM to be auto-submitted by the browser.
            
            return [
                'Success' => true,
                'HtmlContent' => $this->generateAutoSubmitForm($this->baseUrl, $xml)
            ];

        } catch (\Exception $e) {
            Log::error("Kuveyt Turk Sanal POS Error: " . $e->getMessage());
            return ['Success' => false, 'ResponseMessage' => $e->getMessage()];
        }
    }

    private function generateAutoSubmitForm($url, $xml)
    {
        // Encode XML properly? Sanal POS usually expects "AuthenticationXM" field.
        // Special chars might need htmlspecialchars.
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Bank...</title>
</head>
<body onload="document.forms[0].submit()">
    <form method="post" action="{$url}">
        <input type="hidden" name="KuveytTurkVPosMessage" value='{$xml}'>
    </form>
    <p>Redirecting to 3D Secure Payment...</p>
</body>
</html>
HTML;
    }
    
    public function handleCallback($request)
    {
        // Decode callback XML
        $authResponse = $request->input('AuthenticationResponse');
        
        if (!$authResponse) {
             return ['Success' => false, 'Message' => 'No AuthenticationResponse received'];
        }

        try {
            Log::info("Kuveyt Turk 3D Response XML: " . urldecode($authResponse));
            $xml = simplexml_load_string(urldecode($authResponse));
            
            // 00 means 3D authentication was successful
            if ($xml->ResponseCode == '00') {
                return $this->provisionPayment($xml);
            } else {
                 return [
                    'Success' => false,
                    'Message' => '3D Authentication Failed: ' . (string)$xml->ResponseMessage,
                    'Code' => (string)$xml->ResponseCode,
                     'Raw' => $xml
                ];
            }
        } catch (\Exception $e) {
             return ['Success' => false, 'Message' => 'XML Parse Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Step 2: Provision (Actually charge the card after 3D success)
     */
    private function provisionPayment($authXml)
    {
        $amountStr = (string)$authXml->VPosMessage->Amount;
        $orderId = (string)$authXml->MerchantOrderId;
        $md = (string)$authXml->MD; // MD value from 3D Secure
        
        // Hash for Provision: Base64(SHA1(MerchantId + MerchantOrderId + Amount + UserName + HashedPassword))
        $hashedPassword = base64_encode(sha1($this->password, true));
        $hashStr = $this->merchantId . $orderId . $amountStr . $this->username . $hashedPassword;
        $hashData = base64_encode(sha1($hashStr, true));
        
        $provisionXml = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">' .
            '<APIVersion>1.0.0</APIVersion>' .
            '<HashData>' . $hashData . '</HashData>' .
            '<MerchantId>' . $this->merchantId . '</MerchantId>' .
            '<CustomerId>' . $this->customerId . '</CustomerId>' .
            '<UserName>' . $this->username . '</UserName>' .
            '<TransactionType>Sale</TransactionType>' .
            '<InstallmentCount>0</InstallmentCount>' .
            '<Amount>' . $amountStr . '</Amount>' .
            '<MerchantOrderId>' . $orderId . '</MerchantOrderId>' .
            '<TransactionSecurity>3</TransactionSecurity>' . // 3D Secure
            '<KuveytTurkVPosAdditionalData>' .
                '<AdditionalData>' .
                    '<Key>MD</Key>' .
                    '<Data>' . $md . '</Data>' .
                '</AdditionalData>' .
            '</KuveytTurkVPosAdditionalData>' .
        '</KuveytTurkVPosMessage>';

        Log::info("Kuveyt Turk Provision Request XML: " . $provisionXml);

        $provisionUrl = str_replace('ThreeDModelPayGate', 'ThreeDModelProvisionGate', $this->baseUrl);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/xml; charset=utf-8'
            ])->post($provisionUrl, $provisionXml);

            $body = $response->body();
            Log::info("Kuveyt Turk Provision Response XML: " . $body);
            
            // Wait, Kuveyt might wrap response in envelope, so parse carefully
            $resXml = @simplexml_load_string($body);
            
            // Handle error string if not XML
            if (!$resXml) {
                return ['Success' => false, 'Message' => 'Invalid Provision Response Format', 'Raw' => $body];
            }

            if ($resXml->ResponseCode == '00') {
                return [
                    'Success' => true,
                    'Message' => 'Payment Completely Successful!',
                    'OrderId' => $orderId,
                    'Ref' => (string)$resXml->ProvisionNumber,
                    'Raw' => $resXml
                ];
            } else {
                return [
                    'Success' => false,
                    'Message' => 'Provision Failed: ' . (string)$resXml->ResponseMessage,
                    'Code' => (string)$resXml->ResponseCode,
                    'Raw' => $resXml
                ];
            }
        } catch (\Exception $e) {
            Log::error("Kuveyt Turk Provision Error: " . $e->getMessage());
            return ['Success' => false, 'Message' => 'Provision Exception: ' . $e->getMessage()];
        }
    }
}
