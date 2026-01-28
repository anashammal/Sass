<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use App\Models\ProductBatch;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class CheckExpiryAlerts extends Command
{
    protected $signature = 'expiry:check';
    protected $description = 'فحص صلاحية المنتجات وإرسال تنبيهات واتساب';

    public function handle(WhatsAppService $whatsapp)
    {
        $this->info('بدأ فحص المنتجات...');

        $stores = Store::where('is_active', true)->with('owner')->get();

        foreach ($stores as $store) {
            if (!$store->owner || !$store->owner->phone) continue;

            // 1. المنتجات المنتهية (انتهت أمس أو اليوم)
            $expired = ProductBatch::whereHas('product', fn($q) => $q->where('store_id', $store->id))
                ->where('quantity', '>', 0)
                ->whereDate('expiry_date', '<=', Carbon::today())
                ->with('product')
                ->get();

            // 2. المنتجات التي قاربت على الانتهاء
            $near = ProductBatch::whereHas('product', fn($q) => $q->where('store_id', $store->id))
                ->where('quantity', '>', 0)
                ->whereDate('expiry_date', '>', Carbon::today())
                ->whereRaw('expiry_date <= DATE_ADD(NOW(), INTERVAL alert_days DAY)')
                ->with('product')
                ->get();

            if ($expired->isEmpty() && $near->isEmpty()) continue;

            // بناء الرسالة
            $msg = "🚨 *تنبيه من نظام TechSys* 🚨\n";
            $msg .= "المتجر: {$store->name}\n\n";

            if ($expired->count() > 0) {
                $msg .= "🛑 *منتجات انتهت صلاحيتها ({$expired->count()}):*\n";
                foreach ($expired->take(5) as $b) {
                    $msg .= "- {$b->product->name_ar} (انتهى: {$b->expiry_date})\n";
                }
                if ($expired->count() > 5) $msg .= "... والمزيد.\n";
                $msg .= "\n";
            }

            if ($near->count() > 0) {
                $msg .= "⚠️ *منتجات قاربت الانتهاء ({$near->count()}):*\n";
                foreach ($near->take(5) as $b) {
                    $msg .= "- {$b->product->name_ar} (ينتهي: {$b->expiry_date})\n";
                }
            }

            $msg .= "\nيرجى مراجعة لوحة التحكم فوراً.";

            // الإرسال
            $whatsapp->send($store->owner->phone, $msg);
            $this->info("تم إرسال تنبيه لـ {$store->name}");
        }

        $this->info('انتهت العملية.');
    }
}