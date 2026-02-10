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
    protected $signature = 'store:daily-report {--force}';
    protected $description = 'إرسال تقرير النواقص وانتهاء الصلاحية حسب توقيت كل متجر';

    public function handle()
    {
        // 1. جلب جميع المتاجر النشطة التي لديها إعدادات تنبيه
        $stores = Store::where('status', 'active')
                       ->whereNotNull('daily_report_time')
                       ->get();

        foreach ($stores as $store) {
            
            if (!$this->option('force')) {
                // --- تحديد المنطقة الزمنية للمتجر ---
                $storeTimezone = $store->timezone ?? 'Europe/Istanbul'; 

                try {
                    $storeCurrentTime = Carbon::now($storeTimezone)->format('H:i');
                } catch (\Exception $e) {
                    $storeCurrentTime = Carbon::now('Europe/Istanbul')->format('H:i');
                }

                // مقارنة: هل وقت المتجر الآن == وقت التقرير المطلوب؟
                if (mb_substr($store->daily_report_time, 0, 5) !== $storeCurrentTime) {
                    continue; 
                }

                // --- منطق منع التكرار (القفل) ---
                $lockKey = 'daily_report_sent_' . $store->id . '_' . date('Y-m-d');
                if (Cache::has($lockKey)) {
                    continue;
                }
                
                // تفعيل القفل لمدة 20 ساعة
                Cache::put($lockKey, true, now()->addHours(20));
            }

            // إرسال التقرير
            $this->processStore($store);
        }
    }

    public function processStore($store)
    {
        $yesterday = Carbon::yesterday();

        // 1. الملخص المالي لليوم السابق
        $financials = [
            'sales' => \App\Models\Sale::where('store_id', $store->id)->whereDate('created_at', $yesterday)->sum('total'),
            'expenses' => \App\Models\Expense::where('store_id', $store->id)->whereDate('expense_date', $yesterday)->sum('amount'),
        ];
        $financials['profit'] = $financials['sales'] - $financials['expenses']; // تبسيط (يمكن تحسينه لاحقاً بحساب التكلفة الفعلي)

        // 2. المخزون والانتهاء
        $outOfStock = Product::where('store_id', $store->id)
                             ->where('current_stock', '<=', 0)
                             ->take(20)->get();

        $lowStock = Product::where('store_id', $store->id)
                           ->where('current_stock', '>', 0)
                           ->whereColumn('current_stock', '<=', 'alert_quantity')
                           ->take(20)->get();

        $expired = ProductBatch::whereHas('product', fn($q) => $q->where('store_id', $store->id))
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', now())
            ->with('product')
            ->take(20)->get();

        // 3. توليد ملف PDF
        $arabicService = new \App\Services\ArabicTextService();
        $date = $yesterday->format('Y-m-d');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store_owner.reports.daily_report_pdf', [
            'store' => $store,
            'financials' => $financials,
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'expired' => $expired,
            'date' => $date,
            'arabicService' => $arabicService
        ])->setPaper('a4', 'portrait')
          ->setOptions([
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => false,
              'defaultFont' => 'DejaVu Sans'
          ]);

        $filename = 'daily_report_' . $store->id . '_' . $date . '.pdf';
        $directory = public_path('temp_reports');
        if (!file_exists($directory)) mkdir($directory, 0777, true);
        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;
        $pdf->save($filePath);

        $reportUrl = asset('temp_reports/' . $filename);

        // 4. بناء نص الرسالة الأساسي للواتساب
        $msg = "📊 *التقرير اليومي - {$store->name}*\n";
        $msg .= "📅 تاريخ البيانات: " . $date . "\n\n";
        
        $msg .= "💰 *الملخص المالي:*\n";
        $msg .= "- المبيعات: " . number_format($financials['sales'], 2) . "\n";
        $msg .= "- المصاريف: " . number_format($financials['expenses'], 2) . "\n";
        $msg .= "- صافي الربح التقديري: " . number_format($financials['profit'], 2) . "\n\n";

        $msg .= "📋 *المخزون:*\n";
        $msg .= "- منتهي: " . $expired->count() . " منتجات\n";
        $msg .= "- نفذت: " . $outOfStock->count() . " منتجات\n";
        $msg .= "- منخفض: " . $lowStock->count() . " منتجات\n\n";

        $msg .= "📎 *لتحميل التقرير التفصيلي PDF:*\n{$reportUrl}\n\n";
        $msg .= "🔗 [فتح النظام](" . config('app.url') . "/store-owner/dashboard)";

        // 5. الإرسال (واتساب) - استخدام الخدمة الموحدة
        $whatsappSuccess = false;
        if ($store->notify_whatsapp && $store->phone_number) {
            try {
                $whatsappService = new \App\Services\WhatsAppService();
                // إرسال رسالة نصية تحتوي على الرابط
                $whatsappSuccess = $whatsappService->send($store->phone_number, $msg, $store->id);
                
                // اختياري: إرسال الملف نفسه أيضاً إذا أردت
                // $whatsappService->sendFile($store->phone_number, $reportUrl, 'التقرير اليومي', $store->id, $filename);

                if ($whatsappSuccess) {
                     \Log::info("WhatsApp Report Sent: Store {$store->id}");
                } else {
                     \Log::warning("WhatsApp Report Failed (Service returned false): Store {$store->id}");
                }
            } catch (\Exception $e) {
                 \Log::error("WhatsApp Report Failed (Exception): Store {$store->id}", ['error' => $e->getMessage()]);
            }
        }

        // 6. الإرسال (إيميل) - مستقل
        $emailSuccess = false;
        if ($store->notify_email && $store->email) {
            try {
                // محاولة تصحيح اسم المرسل في التقرير
                $subject = "📊 تقرير متجر {$store->name} اليومي ({$date})"; // استخدام كلمة متجر بدلاً من مطعم إذا كانت موجودة

                \Illuminate\Support\Facades\Mail::to($store->email)->send(
                    new \App\Mail\ReportMail(
                        $subject,
                        $msg,
                        $filePath,
                        $filename
                    )
                );
                $emailSuccess = true;
                // \Log::info("Email Report Sent: Store {$store->id}");
            } catch (\Exception $e) {
                \Log::error("Email Report Failed: Store {$store->id}", ['error' => $e->getMessage()]);
            }
        }
        
        // تسجيل ملخص بسيط
        if (!$whatsappSuccess || !$emailSuccess) {
            // \Log::info("Daily Report Status Store {$store->id}: WA=" . ($whatsappSuccess?'OK':'FAIL') . ", Email=" . ($emailSuccess?'OK':'FAIL'));
        }
    }
}