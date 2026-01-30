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

            // الإرسال مع تجاوز SSL ومهلة قصيرة
            $response = Http::withoutVerifying()->timeout(2)->post("{$this->baseUrl}/send-message", [
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
            Log::error("WhatsApp Service Error ({$sessionId}): " . $e->getMessage());
            return false;
        }
    }

    public function sendFile($phone, $fileUrl, $caption = '', $storeId = null, $filename = 'document.pdf')
    {
        $sessionId = $storeId ? "store_{$storeId}" : "system";
        
        $status = $this->getStatus($storeId);
        if (!$status['connected']) {
            Log::warning("WhatsApp sendFile aborted: Session {$sessionId} is not connected.");
            return false;
        }

        try {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '05') && strlen($phone) == 10) { $phone = '966' . substr($phone, 1); }
            if (str_starts_with($phone, '5') && strlen($phone) == 9) { $phone = '966' . $phone; }

            // تجهيز مسار الملف المحلي
            $fullPath = null;
            if (str_contains($fileUrl, 'localhost') || str_contains($fileUrl, '127.0.0.1')) {
                $baseAsset = asset('');
                $relativePath = str_ireplace($baseAsset, '', $fileUrl);
                $fullPath = public_path($relativePath);
                if (!file_exists($fullPath)) {
                    $fileNameOnly = basename($fileUrl);
                    $fullPath = public_path('temp_reports/' . $fileNameOnly);
                }
                Log::info("WhatsApp sendFile: Detected path: " . $fullPath);
            }

            // تنظيف الكود والعودة للطريقة الأكثر استقراراً مع مفتاح 'document'
            $fileData = null;
            if ($fullPath && file_exists($fullPath)) {
                $fileData = base64_encode(file_get_contents($fullPath));
            } else {
                return $this->send($phone, $caption . "\n" . $fileUrl, $storeId);
            }

            $dataUri = 'data:application/pdf;base64,' . $fileData;

            // استخدام المفاتيح الأكثر شيوعاً فقط (تبسيط الطلب لتجنب كراش السيرفر)
            $postData = [
                'session_id' => $sessionId,
                'phone' => $phone,
                'message' => $caption,
                'caption' => $caption,
                'media' => $dataUri, // هذا هو المفتاح القياسي لمعظم سيرفرات Node.js
                'filename' => $filename
            ];

            // إرسال الطلب بشكل صريح جداً كـ JSON
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->asJson()
                ->post("{$this->baseUrl}/send-message", $postData);

            if ($response->successful() && ($response->json('success') || $response->json('status') == 'sent' || $response->json('id'))) {
                Log::info("WhatsApp sendFile success ({$sessionId}) via Simplified-JSON");
                return true;
            }

            Log::error("WhatsApp sendFile Failure ({$sessionId}) - Body: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp Service sendFile Error: " . $e->getMessage());
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
                'session_id' => $sessionId,
                'session' => $sessionId
            ]);
            
            if ($response->successful()) {
                // استخدام الـ Unicode الصحيح لظهور الأسماء العربية في السجل بوضوح
                Log::info("WhatsApp Status ({$sessionId}): " . json_encode($response->json(), JSON_UNESCAPED_UNICODE));
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