<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Get real-time or cached exchange rates for the given base currency.
     * Uses ExchangeRate-API (free, no key required for basic open endpoint, 
     * but we will use the standard structure. Can be upgraded easily).
     */
    public function getRates($baseCurrencyCode)
    {
        $baseCurrencyCode = strtoupper($baseCurrencyCode);
        $cacheKey = "exchange_rates_{$baseCurrencyCode}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($baseCurrencyCode) {
            try {
                // Using reliable open API for exchange rates
                $response = Http::withoutVerifying()->get("https://open.er-api.com/v6/latest/{$baseCurrencyCode}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'success' => true,
                        'rates' => $data['rates'] ?? [],
                        'base' => $baseCurrencyCode,
                        'last_update' => now()->toDateTimeString(),
                    ];
                }
                
                Log::error("Failed to fetch exchange rates for {$baseCurrencyCode}: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Exception in ExchangeRateService: " . $e->getMessage());
            }

            return [
                'success' => false,
                'rates' => [],
                'base' => $baseCurrencyCode,
                'last_update' => null,
            ];
        });
    }

    /**
     * Convert an amount from one currency to another using the latest rates.
     */
    public function convert($amount, $fromCurrencyCode, $toCurrencyCode)
    {
        if ($fromCurrencyCode === $toCurrencyCode) {
            return $amount;
        }

        $rate = $this->getExchangeRate($fromCurrencyCode, $toCurrencyCode);
        
        if ($rate !== null) {
            return $amount * $rate;
        }

        return null; // Conversion failed
    }

    /**
     * Get the exchange rate (multiplier) from one currency to another.
     */
    public function getExchangeRate($fromCurrencyCode, $toCurrencyCode)
    {
        if ($fromCurrencyCode === $toCurrencyCode) {
            return 1.0;
        }

        $ratesData = $this->getRates($fromCurrencyCode);
        
        if ($ratesData['success'] && isset($ratesData['rates'][strtoupper($toCurrencyCode)])) {
            return (float) $ratesData['rates'][strtoupper($toCurrencyCode)];
        }

        return null;
    }

    /**
     * Clear cached rates for a currency
     */
    public function clearCache($baseCurrencyCode)
    {
        Cache::forget("exchange_rates_" . strtoupper($baseCurrencyCode));
    }
}
