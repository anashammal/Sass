<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Sale;
use App\Models\Payment; // تأكد من وجود هذا السطر
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftController extends Controller
{
    // فحص الحالة
    public function checkStatus()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['has_open_shift' => false]);

        $shift = Shift::where('user_id', $user->id)
                      ->where('store_id', $user->store->id)
                      ->where('status', 'open')
                      ->latest()
                      ->first();
        
        return response()->json([
            'has_open_shift' => $shift ? true : false,
            'shift' => $shift
        ]);
    }

    // فتح الصندوق
    public function openShift(Request $request)
    {
        $request->validate(['start_cash' => 'required|numeric|min:0']);
        $user = Auth::user();

        // إغلاق أي صناديق سابقة معلقة
        Shift::where('user_id', $user->id)
             ->where('status', 'open')
             ->update(['status' => 'closed', 'closed_at' => now(), 'end_cash' => 0]);

        // إنشاء الوردية
        Shift::create([
            'store_id' => $user->store->id,
            'user_id' => $user->id,
            'start_cash' => $request->start_cash,
            'status' => 'open',
            'opened_at' => now()
        ]);

        return response()->json(['message' => 'تم فتح الصندوق']);
    }

    // 🟢 هذه هي الدالة المفقودة والمهمة جداً للتقرير 🟢
    public function getSummary()
    {
        $user = Auth::user();
        $shift = Shift::where('user_id', $user->id)->where('status', 'open')->latest()->first();

        if (!$shift) return response()->json(['error' => 'لا يوجد صندوق مفتوح'], 404);

        // جلب الفواتير لهذه الوردية
        $salesQuery = Sale::where('user_id', $user->id)
                          ->where('created_at', '>=', $shift->opened_at);
        
        $salesIds = $salesQuery->pluck('id');
        
        // 1. حساب مبيعات الدين
        $creditSales = $salesQuery->sum('due'); 

        // 2. حساب تفاصيل المدفوعات من جدول Payments بدقة
        $cashSales = Payment::whereIn('sale_id', $salesIds)->where('method', 'cash')->sum('amount');
        $cardSales = Payment::whereIn('sale_id', $salesIds)->where('method', 'card')->sum('amount');
        $bankSales = Payment::whereIn('sale_id', $salesIds)->where('method', 'bank')->sum('amount');

        // 3. المتوقع في الدرج = (العهدة + الكاش المقبوض فقط)
        $expectedCash = $shift->start_cash + $cashSales;

        // 4. المجموع الكلي للمبيعات
        $totalSales = $cashSales + $cardSales + $bankSales + $creditSales;

        // 5. تفاصيل العملات الأجنبية في الدرج (الكاش فقط)
        $foreignPayments = Payment::with('currency')
            ->whereIn('sale_id', $salesIds)
            ->where('method', 'cash')
            ->whereNotNull('amount_in_foreign_currency')
            ->get();

        $foreignCurrencies = [];
        foreach ($foreignPayments as $payment) {
            $currencyId = $payment->currency_id;
            if (!$currencyId) continue;
            
            if (!isset($foreignCurrencies[$currencyId])) {
                $foreignCurrencies[$currencyId] = [
                    'code' => $payment->currency->code,
                    'symbol' => $payment->currency->symbol,
                    'amount' => 0
                ];
            }
            $foreignCurrencies[$currencyId]['amount'] += $payment->amount_in_foreign_currency;
        }

        return response()->json([
            'start_cash' => number_format($shift->start_cash, 2),
            'cash_sales' => number_format($cashSales, 2),
            'card_sales' => number_format($cardSales, 2),
            'bank_sales' => number_format($bankSales, 2),
            'credit_sales' => number_format($creditSales, 2),
            'total_sales' => number_format($totalSales, 2),
            'expected_cash' => $expectedCash, // رقم خام للحسابات
            'foreign_currencies' => array_values($foreignCurrencies), // قائمة العملات
            // ✅ إصلاح التوقيت (+3 ساعات)
            'opened_at' => Carbon::parse($shift->opened_at)->addHours(3)->format('Y-m-d h:i A')
        ]);
    }

    // إغلاق الصندوق
    public function closeShift(Request $request)
    {
        $request->validate(['end_cash' => 'required|numeric']);
        $user = Auth::user();
        
        // البحث بنفس شروط الفتح والفحص
        $shift = Shift::where('user_id', $user->id)
                      ->where('store_id', $user->store->id)
                      ->where('status', 'open')
                      ->latest()
                      ->first();
        
        if (!$shift) return response()->json(['error' => 'لا يوجد صندوق مفتوح لإغلاقه'], 404);

        // جلب معرفات الفواتير لهذه الوردية
        $salesIds = Sale::where('user_id', $user->id)
                        ->where('created_at', '>=', $shift->opened_at)
                        ->pluck('id');

        // جمع مبيعات الكاش فقط
        $cashSales = Payment::whereIn('sale_id', $salesIds)
                            ->where('method', 'cash')
                            ->sum('amount');
        
        $expected = $shift->start_cash + $cashSales;

        $shift->update([
            'end_cash' => $request->end_cash,
            'expected_cash' => $expected,
            'status' => 'closed',
            'closed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'difference' => $request->end_cash - $expected
        ]);
    }
}