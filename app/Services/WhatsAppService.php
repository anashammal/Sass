<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;

    public function __construct()
    {
        // الرابط الثابت للسيرفر
        $this->baseUrl = config('services.whatsapp.url');
    }

    /**
     * دالة الإرسال الذكية (لا توقف النظام إذا فشلت)
     */
    public function send($phone, $message, $storeId = null)
    {
        // تحديد الجلسة (System للأدمن، store_X للمتاجر)
        $sessionId = $storeId ? "store_{$storeId}" : "system";

        try {
            // تنظيف الرقم وإصلاحه للسعودية
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '05') && strlen($phone) == 10) {
                $phone = '966' . substr($phone, 1);
            }
            if (str_starts_with($phone, '5') && strlen($phone) == 9) {
                $phone = '966' . $phone;
            }

            // الإرسال مع تجاوز SSL ومهلة قصيرة (5 ثواني)
            // لكي لا يعلق النظام إذا السيرفر طافي
            $response = Http::withoutVerifying()->timeout(10)->post("{$this->baseUrl}/send-message", [
    'phone' => $phone,
    'message' => $message,
    'session_id' => $sessionId
]);

if ($response->successful() && ($response->json('success') === true)) {
    return true;
}

Log::error("WhatsApp send failed ({$sessionId}): " . $response->body());
return false;


        } catch (\Exception $e) {
            // هنا السر: نسجل الخطأ ولكن نرجع False بصمت
            // لكي يكمل النظام عمله ويرسل الإيميل
            Log::error("WhatsApp Service Error ({$sessionId}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * جلب الحالة (يحل مشكلة الخدمة غير متاحة)
     */
    public function getStatus($storeId = null)
    {
        $sessionId = $storeId ? "store_{$storeId}" : "system";
        
        try {
            $response = Http::withoutVerifying()->timeout(5)->get("{$this->baseUrl}/session-status", [
                'session_id' => $sessionId
            ]);
            
            if ($response->successful()) {
                Log::info("WhatsApp Service Response ({$sessionId}): " . $response->body());
                return $response->json();
            }
            
            return ['connected' => false, 'qr' => null];

        } catch (\Exception $e) {
            return ['connected' => false, 'qr' => null, 'error' => true];
        }
    }

    public function logout($storeId = null)
    {
        $sessionId = $storeId ? "store_{$storeId}" : "system";
        try {
            Http::withoutVerifying()->timeout(5)->post("{$this->baseUrl}/logout", [
                'session_id' => $sessionId
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}