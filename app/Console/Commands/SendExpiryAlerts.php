<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductBatch;
use App\Models\User; // أو StoreOwner إذا كان لديك موديل منفصل
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpiryAlertMail;

class SendExpiryAlerts extends Command
{
    protected $signature = 'alerts:send-expiry';
    protected $description = 'Send email alerts for expiring products';

    public function handle()
    {
        // 1. جلب كل المتاجر وأصحابها
        // (سنفترض أن كل مستخدم له store_id هو صاحب متجر ويحتاج تنبيه)
        $owners = User::whereNotNull('store_id')->get();

        foreach ($owners as $owner) {
            // 2. فحص منتجات هذا المتجر
            $expiringBatches = ProductBatch::whereHas('product', function($q) use ($owner) {
                    $q->where('store_id', $owner->store_id);
                })
                ->where('quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereRaw('expiry_date <= DATE_ADD(NOW(), INTERVAL alert_days DAY)')
                ->with('product')
                ->get();

            if ($expiringBatches->count() > 0) {
                // 3. إرسال الإيميل
                if ($owner->email) {
                    Mail::to($owner->email)->send(new ExpiryAlertMail($expiringBatches));
                    $this->info("Email sent to: " . $owner->email);
                }
            }
        }
        $this->info('Done.');
    }
}