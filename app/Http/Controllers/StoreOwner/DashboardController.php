<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $storeId = Auth::user()->store_id;

        // 1. فحص انتهاء الصلاحية (للنافذة)
        $expiredBatches = ProductBatch::whereHas('product', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', Carbon::now()->addDays(30)) // تنبيه قبل 30 يوم
            ->with('product')
            ->get();

        // 2. فحص المخزون المنخفض (للنافذة) - الإضافة الجديدة 🔥
        $lowStockProducts = Product::where('store_id', $storeId)
            ->whereColumn('current_stock', '<=', 'alert_quantity')
            ->get();

        // دمج التنبيهات للنافذة المنبثقة
        $showPopup = false;
        if (($expiredBatches->count() > 0 || $lowStockProducts->count() > 0) && !session('expiry_popup_seen')) {
            $showPopup = true;
        }

        // --- باقي إحصائيات الداشبورد (كما هي) ---
        $stats = [
            'products_count' => Product::where('store_id', $storeId)->count(),
            'low_stock_count' => $lowStockProducts->count(), // نستخدم المتغير الذي جلبناه
            // ... يمكنك إضافة المزيد هنا
        ];

        return view('store_owner.dashboard', compact(
            'stats', 
            'expiredBatches', 
            'lowStockProducts', // ✅ تمرير منتجات المخزون المنخفض للعرض
            'showPopup'
        ));
    }
}