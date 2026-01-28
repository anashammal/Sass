<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SendStoreDailyReports extends Command
{
    protected $signature = 'store:daily-report';
    protected $description = 'إرسال تقرير النواقص وانتهاء الصلاحية حسب توقيت كل متجر';

    public function handle()
    {
        // 1. جلب جميع المتاجر النشطة التي لديها إعدادات تنبيه
        // (نحضر الجميع لأننا سنفحص توقيت كل واحد على حدة)
        $stores = Store::where('status', 'active') // تأكد أن لديك عمود status أو احذفه
                       ->whereNotNull('daily_report_time')
                       ->get();

        foreach ($stores as $store) {
            
            // --- 🔥 تحديد المنطقة الزمنية للمتجر 🔥 ---
            // إذا كان لديك عمود 'timezone' في جدول المتاجر، نستخدمه.
            // إذا لم يوجد، نستخدم التوقيت الافتراضي (مثلاً Europe/Istanbul أو Asia/Riyadh)
            $storeTimezone = $store->timezone ?? 'Europe/Istanbul'; 

            try {
                // معرفة الوقت الحالي "عند المتجر"
                $storeCurrentTime = Carbon::now($storeTimezone)->format('H:i');
            } catch (\Exception $e) {
                // في حال كان اسم المنطقة الزمنية خطأ، نعود للافتراضي
                $storeCurrentTime = Carbon::now('Europe/Istanbul')->format('H:i');
            }

            // مقارنة: هل وقت المتجر الآن == وقت التقرير المطلوب؟
            // نستخدم mb_substr لضمان مطابقة التنسيق (09:00 مع 09:00)
            if (substr($store->daily_report_time, 0, 5) !== $storeCurrentTime) {
                continue; // الوقت لم يحن لهذا المتجر بعد
            }

            // --- 🔒 منطق منع التكرار (القفل) ---
            $lockKey = 'daily_report_sent_' . $store->id . '_' . date('Y-m-d');
            if (Cache::has($lockKey)) {
                continue;
            }

            // إرسال التقرير
            $this->processStore($store);

            // تفعيل القفل لمدة 20 ساعة
            Cache::put($lockKey, true, now()->addHours(20));
        }
    }

    public function processStore($store)
    {
        // 1. جلب المنتجات (الأسماء وليس العدد فقط)
        $outOfStock = Product::where('store_id', $store->id)
                             ->where('current_stock', '<=', 0)
                             ->take(10)->get(); // نأخذ أول 10 فقط لمنع رسالة طويلة جداً

        $lowStock = Product::where('store_id', $store->id)
                           ->where('current_stock', '>', 0)
                           ->whereColumn('current_stock', '<=', 'alert_quantity')
                           ->take(10)->get();

        $expired = ProductBatch::whereHas('product', fn($q) => $q->where('store_id', $store->id))
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', now())
            ->with('product')
            ->take(10)->get();

        // إذا لا يوجد شيء، لا ترسل
        if ($outOfStock->isEmpty() && $lowStock->isEmpty() && $expired->isEmpty()) {
            return;
        }

        // 2. بناء الرسالة
        $msg = "📊 *التقرير اليومي - {$store->name}*\n";
        $msg .= "📅 " . date('Y-m-d') . "\n";
        $msg .= "⏰ " . now($store->timezone ?? 'Europe/Istanbul')->format('H:i') . "\n\n";

        if ($expired->count() > 0) {
            $msg .= "🔴 *منتهية الصلاحية:*\n";
            foreach($expired as $b) {
                $msg .= "- {$b->product->name_ar} (انتهى: {$b->expiry_date})\n";
            }
            $msg .= "\n";
        }

        if ($outOfStock->count() > 0) {
            $msg .= "❌ *منتجات نفذت:*\n";
            foreach($outOfStock as $p) {
                $msg .= "- {$p->name_ar}\n";
            }
            $msg .= "\n";
        }

        if ($lowStock->count() > 0) {
            $msg .= "⚠️ *مخزون منخفض:*\n";
            foreach($lowStock as $p) {
                $stock = (float)$p->current_stock; // إزالة الأصفار
                $msg .= "- {$p->name_ar} (باقي: {$stock})\n";
            }
            $msg .= "\n";
        }

        // 🔥 الرابط التشعبي المباشر للمنتجات 🔥
        // الرابط يوجه لصفحة المنتجات، ويمكنك إضافة فلتر إذا كان مدعوماً في الفرونت
        $url = "http://tech-sys.online/store-owner/products";
        $msg .= "🔗 [عرض التفاصيل في النظام]({$url})";

        // 3. الإرسال (واتساب)
        if ($store->notify_whatsapp && $store->phone_number) {
            try {
                Http::timeout(5)->post('https://wa.tech-sys.online/send-message', [
                    'phone' => $store->phone_number,
                    'message' => $msg,
                    'session_id' => 'store_' . $store->id
                ]);
            } catch (\Exception $e) { }
        }

        // 4. الإرسال (إيميل)
        if ($store->notify_email && $store->email) {
            try {
                Mail::raw($msg, function ($mail) use ($store) {
                    $mail->to($store->email)
                         ->subject("📊 تقرير الحالة اليومي - {$store->name}");
                });
            } catch (\Exception $e) { }
        }
    }
}