<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductBatch;
use App\Models\Product; // ✅ ضروري لإحضار موديل المنتجات

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Paginator::useBootstrap();

        if (env('APP_ENV') === 'production' || env('FORCE_HTTPS', false) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
            URL::forceScheme('https');
        }

        $defaultConnection = Config::get('database.default');
        Config::set("database.connections.{$defaultConnection}.username", env('DB_USERNAME'));
        Config::set("database.connections.{$defaultConnection}.password", env('DB_PASSWORD'));

        // ============================================================
        // 4. مشاركة تنبيهات (الصلاحية + نقص الكمية)
        // ============================================================
        View::composer('*', function ($view) {
            if (Auth::check() && ($user = Auth::user()) && $user->store) {
                
                $storeId = $user->store->id;
                
                // --- أولاً: تنبيهات الصلاحية (كما هي سابقاً) ---
                $thresholdDate = now()->addDays(30)->endOfDay(); 

                $batches = ProductBatch::whereHas('product', function($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    })
                    ->where('quantity', '>', 0)
                    ->whereNotNull('expiry_date')
                    ->with('product')
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                $alerts = [
                    'expired' => [],
                    'near' => [],
                    'low_stock' => [] // ✅ مصفوفة جديدة لنقص الكمية
                ];

                foreach ($batches as $batch) {
                    $diff = now()->startOfDay()->diffInDays($batch->expiry_date, false);
                    $batch->days_remaining_calculated = intval($diff);
                    $limit = $batch->product->expiry_warning_days ?? 30;

                    if ($diff < 0) {
                        $alerts['expired'][] = $batch;
                    } elseif ($diff <= $limit) {
                        $alerts['near'][] = $batch;
                    }
                }

                // --- ثانياً: تنبيهات نقص الكمية (الكود الجديد) ---
                // نجلب المنتجات التي كميتها الحالية أقل من أو تساوي حد التنبيه
                $lowStockProducts = Product::where('store_id', $storeId)
                    ->where('is_active', true)
                    ->whereColumn('current_stock', '<=', 'alert_quantity') // المقارنة بين العمودين
                    ->where('current_stock', '>', 0) // استبعاد المنتهي تماماً (اختياري)
                    ->get();

                foreach($lowStockProducts as $prod) {
                    $alerts['low_stock'][] = $prod;
                }

                // تمرير كل البيانات
                $view->with('expiryAlerts', [
                    'expired' => collect($alerts['expired']),
                    'near' => collect($alerts['near']),
                    'low_stock' => collect($alerts['low_stock']) // ✅ إرسال قائمة النقص
                ]);
            }
        });
    }
}