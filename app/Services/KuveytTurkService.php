<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KuveytTurkService
{
    private $baseUrl;
    private $identityUrl;
    private $clientId;     // API Application Client ID (GUID/String)
    private $merchantId;   // Merchant Id
    private $username;     // API User
    private $password;     // API Password / Client Secret
    private $privateKey;   // RSA Private Key
    
    public function __construct()
    {
        $env = config('services.kuveyt_turk.env', 'production');
        
        if ($env === 'sandbox') {
            $this->baseUrl = 'https://prep-gateway.kuveytturk.com.tr/v1/api';
            $this->identityUrl = 'https://prep-identity.kuveytturk.com.tr/connect/token';
        } else {
            $this->baseUrl = 'https://gateway.kuveytturk.com.tr/v1/api'; 
            $this->identityUrl = 'https://identity.kuveytturk.com.tr/connect/token';
        }

        $this->clientId = config('services.kuveyt_turk.client_id'); 
        $this->merchantId = config('services.kuveyt_turk.merchant_id');
        $this->username = config('services.kuveyt_turk.username');
        $this->password = config('services.kuveyt_turk.password');
        
        $pk = config('services.kuveyt_turk.private_key');
        if($pk) {
            $this->privateKey = str_replace(['\n', '\r'], ["\n", "\r"], $pk);
        }
    }

    /**
     * Get Access Token using standard Client Credentials flow
     */
    public function getAccessToken()
    {
        try {
            // According to Kuveyt Turk Docs:
            // Grant Type: client_credentials
            // Auth: Client ID + Client Secret
            // Signature: All requests must be signed.

            $payload = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->password, // Using 'password' env content as Client Secret
                'scope' => 'public' 
            ];

            // Generate signature for the body
            $bodyString = http_build_query($payload);
            $signature = $this->generateRawSignature($bodyString);

            Log::info("Kuveyt Turk: Requesting Token...", ['client_id' => $this->clientId]);

            $response = Http::withoutVerifying()
                ->asForm()
                ->withHeaders(['Signature' => $signature])
                ->post($this->identityUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            Log::error("Kuveyt Turk Token Failed: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Kuveyt Turk Token Exception: " . $e->getMessage());
            return null;
        }
    }

    public function generateRawSignature($dataToSign)
    {
        if (!$this->privateKey) {
            Log::error("Kuveyt Turk: Missing Private Key");
            return null;
        }
        
        $keyResource = openssl_get_privatekey($this->privateKey);
        if (!$keyResource) {
            Log::error("Kuveyt Turk: Invalid Private Key");
            return null;
        }

        $signature = '';
        $success = openssl_sign($dataToSign, $signature, $keyResource, "sha256WithRSAEncryption");
        
        if (!$success) {
            Log::error("Kuveyt Turk: Signing Failed");
            return null;
        }

        return base64_encode($signature);
    }
    
    // Helper for backward compatibility
    public function generateSignature($accessToken, $jsonBody) {
        return $this->generateRawSignature($accessToken . $jsonBody);
    }

    /**
     * Start 3D Secure Payment (Provision)
     */
    public function startPayment($data)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['Success' => false, 'ResponseMessage' => 'Failed to obtain Access Token. Please verify Client ID and Secret in .env from API Market.'];
        }

        $payload = [
            "RequestHeader" => [
                "Command" => "Provision",
                "DeviceId" => "TechSysWeb",
                "ClientRequestId" => $data['order_id'],
            ],
            "MerchantOrderId" => $data['order_id'],
            "ProductData" => [
                "Amount" => $data['amount'],
                "CurrencyCode" => "0949", 
            ],
            "CustomerData" => [
                "CustomerCode" => "cust_123", 
            ],
            "CardData" => [
                "CardNumber" => $data['card_number'],
                "CardHolderName" => $data['card_holder_name'],
                "ExpiryDateMonth" => $data['expire_month'], 
                "ExpiryDateYear" => $data['expire_year'],
                "Cvv2Code" => $data['cvv'],
                "CardType" => "CreditCard"
            ],
            "PaymentType" => "Sale",
            "ProvisionType" => "ThreeD", 
            "InstallmentCount" => 0,
            "CallbackUrl" => route('k-test.callback'),
        ];

        $jsonPayload = json_encode($payload);
        $signature = $this->generateSignature($accessToken, $jsonPayload);
        
        if (!$signature) {
             return ['Success' => false, 'ResponseMessage' => 'Failed to generate Signature. Check Private Key.'];
        }

        try {
            Log::info("Kuveyt Turk: Sending Provision Request...");
            
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Signature' => $signature
                ])
                ->post($this->baseUrl . '/Provision', $payload);
            
            Log::info("Kuveyt Turk Response: " . $response->status() . " " . $response->body());

            $result = $response->json();
            
            if ($result === null && $response->successful()) {
                return ['Success' => true, 'Content' => $response->body()];
            }
            
            if ($response->failed()) {
                return [
                    'Success' => false, 
                    'ResponseMessage' => 'HTTP Error: ' . $response->status() . ' Body: ' . substr($response->body(), 0, 200),
                    'ResponseCode' => $response->status()
                ];
            }

            return $result;
            
        } catch (\Exception $e) {
            Log::error("Kuveyt Turk API Error: " . $e->getMessage());
            return ['Success' => false, 'ResponseMessage' => $e->getMessage()];
        }
    }
}
