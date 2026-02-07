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

            // تجهيز مسار الملف المحلي (يعمل محلياً و أونلاين إذا كان الملف على نفس السيرفر)
            $fullPath = null;
            
            try {
                // محاولة تحويل الرابط إلى مسار محلي
                $baseAsset = asset(''); // http://domain.com/
                
                // تنظيف البروتوكول (http/https) لتجنب مشاكل المطابقة
                $cleanUrl = str_replace(['http://', 'https://'], '', $fileUrl);
                $cleanBase = str_replace(['http://', 'https://'], '', $baseAsset);
                
                // الحصول على المسار النسبي (مثل: temp_reports/file.pdf)
                $relativePath = str_ireplace($cleanBase, '', $cleanUrl);
                $relativePath = ltrim($relativePath, '/\\');
                
                // تجربة المسار المباشر
                $checkPath = public_path($relativePath);
                if (file_exists($checkPath)) {
                    $fullPath = $checkPath;
                } else {
                    // تجربة البحث في مجلد temp_reports مباشرة إذا فشل المسار النسبي
                    $fileNameOnly = basename($fileUrl);
                    $checkPath2 = public_path('temp_reports/' . $fileNameOnly);
                    if (file_exists($checkPath2)) {
                        $fullPath = $checkPath2;
                    }
                }

                if ($fullPath) {
                    Log::info("WhatsApp sendFile: Detected local path (Online/Local): " . $fullPath);
                }
            } catch (\Exception $e) {
                Log::warning("WhatsApp sendFile: Path resolution failed: " . $e->getMessage());
            }

            // تنظيف الكود - نعتمد على المسار المحلي أولاً لتوفير الذاكرة والوقت
            $payload = [
                'session_id' => $sessionId,
                'phone' => $phone,
                'message' => $caption,
                'caption' => $caption,
                'filename' => basename($filename)
            ];

            // 🟢 التعديل الجديد: إرسال الـ Base64 دائماً كاجراء احتياطي (Fallback)
            // لأن السيرفر (WSL) قد لا يرى المسار C:\xampp
            try {
                // زيادة حدود الذاكرة والوقت للملفات الضخمة (1GB)
                ini_set('memory_limit', '1536M'); // زيادة إلى 1.5GB للأمان
                set_time_limit(0);

                // تحديد المصدر (ملف محلي أو رابط)
                $contentSource = ($fullPath && file_exists($fullPath)) ? $fullPath : $fileUrl;
                
                $fileContent = file_get_contents($contentSource);
                
                if ($fileContent === false) throw new \Exception("Could not read file from source");
                
                $size = strlen($fileContent);
                Log::info("WhatsApp sendFile: Reading Content (${contentSource}). Size: " . round($size / 1024 / 1024, 2) . " MB");

                $fileData = base64_encode($fileContent);
                // استخدام المفتاح standard 'media'
                $payload['media'] = 'data:application/pdf;base64,' . $fileData;
            } catch (\Exception $e) {
                Log::error("WhatsApp sendFile Error reading file: " . $e->getMessage());
                return false;
            }

            // إضافة مسار الملف كخيار "تحسين" (Optimization Hint)
            if ($fullPath && file_exists($fullPath)) {
                Log::info("WhatsApp sendFile: Adding path hint -> " . $fullPath);
                $payload['file_path'] = $fullPath; 
            } else {
                 Log::warning("WhatsApp sendFile: Local file path not available, relying on Base64.");
            }

            // ✅ ترميز JSON يدوي للتأكد من خلوه من الأخطاء
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonPayload === false) {
                Log::error("WhatsApp sendFile JSON Encoding Error: " . json_last_error_msg());
                return false;
            }

            // زيادة وقت المهلة للملفات الكبيرة
            set_time_limit(0); 
            
            // إرسال الطلب بشكل صريح جداً (استخدام send لتفادي أي تداخل من دالة post)
            $response = Http::withoutVerifying()
                ->timeout(600) // وقت كافي جداً (10 دقائق)
                ->withBody($jsonPayload, 'application/json')
                ->send('POST', "{$this->baseUrl}/send-message");

            if ($response->successful() && ($response->json('success') || $response->json('status') == 'sent' || $response->json('id'))) {
                Log::info("WhatsApp sendFile success ({$sessionId})");
                return true;
            }

            Log::error("WhatsApp sendFile Failure ({$sessionId}) - Status: " . $response->status() . " - Body: " . substr($response->body(), 0, 500));
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
            
            Log::warning("WhatsApp Status Failed ({$sessionId}): " . $response->status() . " Body: " . $response->body());
            return ['connected' => false, 'qr' => null];

        } catch (\Exception $e) {
            return ['connected' => false, 'qr' => null, 'error' => true];
        }
    }

    public function logout($storeId = null)
    {
        $sessionId = $storeId ? "store_{$storeId}" : "system";
        // قائمة بنهايات المسارات المحتملة لعملية الخروج في سيرفرات واتساب المختلفة
        $endpoints = [
            "/logout",
            "/delete-session",
            "/session/terminate"
        ];

        // حمولة الطلب الموحدة
        $payload = [
            'session_id' => $sessionId,
            'session' => $sessionId,
            'id' => $sessionId
        ];
        
        $jsonPayload = json_encode($payload);

        foreach ($endpoints as $endpoint) {
            try {
                $url = "{$this->baseUrl}{$endpoint}";
                
                // استخدام send('POST') الصريحة كما فعلنا في الإرسال
                $response = Http::withoutVerifying()
                    ->timeout(5)
                    ->withBody($jsonPayload, 'application/json')
                    ->send('POST', $url);

                if ($response->successful() && ($response->json('success') || $response->json('status') === true)) {
                    Log::info("WhatsApp Logout Success ({$sessionId}) via {$endpoint}");
                    return true;
                }
                
                Log::warning("WhatsApp Logout Attempt Failed ({$sessionId}) via {$endpoint}: " . $response->status() . " Body: " . $response->body());

            } catch (\Exception $e) {
                Log::error("WhatsApp Logout Error ({$sessionId}) via {$endpoint}: " . $e->getMessage());
            }
        }

        return false;
    }
}