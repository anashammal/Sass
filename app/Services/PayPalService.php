<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $mode = env('PAYPAL_MODE', 'sandbox');
        $this->baseUrl = ($mode === 'live') 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
        
        $this->clientId = env('PAYPAL_CLIENT_ID');
        $this->clientSecret = env('PAYPAL_CLIENT_SECRET');
    }

    /**
     * Get Access Token from PayPal
     */
    public function getAccessToken()
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        Log::error("PayPal Auth Error: " . $response->body());
        return null;
    }

    /**
     * Create Order
     */
    public function createOrder($amount, $currency = 'USD', $referenceId = null)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $referenceId ?? uniqid(),
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format((float)$amount, 2, '.', '')
                        ]
                    ]
                ],
                'application_context' => [
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW'
                ]
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("PayPal Create Order Error: " . $response->body());
        return null;
    }

    /**
     * Capture Order (Confirm Payment)
     */
    public function captureOrder($paypalOrderId)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture", []);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("PayPal Capture Order Error: " . $response->body());
        return null;
    }
}
