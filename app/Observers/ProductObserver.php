<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // ✅ إضافة مهمة

class ProductObserver
{
    public function updated(Product $product)
    {
        // التحقق: هل نقص المخزون؟ وهل وصل للحد؟
        if ($product->isDirty('current_stock') && $product->current_stock < $product->getOriginal('current_stock')) {
            if ($product->current_stock <= $product->alert_quantity) {
                $this->sendAlert($product);
            }
        }
    }

    protected function sendAlert($product)
    {
        // 🔥🔥🔥 الحل لمشكلة التكرار 🔥🔥🔥
        // نضع "بصمة" للمنتج والكمية الحالية
        // إذا حاول النظام الإرسال لنفس المنتج ونفس الكمية خلال 10 ثواني، سيتم منعه
        $cacheKey = 'alert_sent_' . $product->id . '_' . $product->current_stock;

        if (Cache::has($cacheKey)) {
            return; // 🛑 توقف! تم الإرسال قبل قليل
        }

        // قفل الإرسال لمدة 30 ثانية
        Cache::put($cacheKey, true, now()->addSeconds(30)); 
        // 🔥🔥🔥 نهاية حل التكرار 🔥🔥🔥


        $store = $product->store;
        
        // 1. تجهيز الرسالة
        $isOutOfStock = $product->current_stock <= 0;
        $title = $isOutOfStock ? "🔴 نفذت الكمية (مخزون منتهي)" : "⚠️ تنبيه مخزون منخفض";
        
        $msg = "{$title}\n";
        $msg .= "المتجر: {$store->name}\n";
        $msg .= "📦 المنتج: {$product->name_ar}\n";
        $msg .= "📉 الكمية الحالية: " . (float)$product->current_stock . "\n";
        $msg .= "🛑 حد التنبيه: " . (float)$product->alert_quantity;

        // =================================================
        // أولاً: إرسال الإيميل
        // =================================================
        if ($store->notify_email && $store->email) {
            try {
                Mail::raw($msg, function ($m) use ($store, $title) {
                    $m->to($store->email)->subject($title);
                });
            } catch (\Exception $e) {
                Log::error("Mail Error: " . $e->getMessage());
            }
        }

        // =================================================
        // ثانياً: إرسال الواتساب (مع التحقق الذكي)
        // =================================================
        if ($store->notify_whatsapp && $store->phone_number) {
            
            // تنظيف الرقم
            $phone = preg_replace('/[^0-9]/', '', $store->phone_number);
            if (str_starts_with($phone, '00')) $phone = substr($phone, 2);
            
            try {
                // المحاولة لمدة 3 ثواني
                $response = Http::timeout(3)->post('http://localhost:3000/send-message', [
                    'phone' => $phone, 
                    'message' => $msg
                ]);

                if($response->failed()) {
                    Log::error("WhatsApp API Failed: " . $response->body());
                } else {
                    Log::info("WhatsApp Sent Successfully to: " . $phone);
                }

            } catch (\Exception $e) {
                Log::error("WhatsApp Connection Error: " . $e->getMessage());
            }
        }
    }
}