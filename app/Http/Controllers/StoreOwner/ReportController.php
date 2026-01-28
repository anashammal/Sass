<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Sale;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function shifts(Request $request)
    {
        $storeId = Auth::user()->store->id;

        // جلب الورديات المغلقة (أو المفتوحة) مع الفلترة
        $query = Shift::where('store_id', $storeId)->with('user')->latest();

        // فلتر بالتاريخ
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }
        // فلتر بالموظف
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $shifts = $query->paginate(15);

        // معالجة البيانات لكل وردية
        $shifts->getCollection()->transform(function ($shift) {
            // تحديد وقت النهاية (إذا مفتوح نعتبر الوقت الحالي هو النهاية للحسابات)
            $endTime = $shift->closed_at ?? now();

            // 1. حساب المدة
            $start = Carbon::parse($shift->opened_at);
            $end = Carbon::parse($endTime);
            $shift->duration = $start->diff($end)->format('%H ساعة و %I دقيقة');

            // 2. جلب المبيعات المرتبطة بهذه الوردية
            $salesQuery = Sale::where('user_id', $shift->user_id)
                ->whereBetween('created_at', [$shift->opened_at, $endTime])
                ->with(['payments', 'items.product']); // نحتاج المنتجات لحساب التكلفة

            $sales = $salesQuery->get();

            // 3. تفصيل المبيعات
            $shift->total_cash_sales = 0;
            $shift->total_card_sales = 0;
            $shift->total_bank_sales = 0;
            
            // نجمع من جدول المدفوعات لضمان الدقة
            foreach ($sales as $sale) {
                foreach ($sale->payments as $payment) {
                    if ($payment->method == 'cash') $shift->total_cash_sales += $payment->amount;
                    if ($payment->method == 'card') $shift->total_card_sales += $payment->amount;
                    if ($payment->method == 'bank') $shift->total_bank_sales += $payment->amount;
                }
            }

            // مبيعات الدين (التي لم تدفع)
            $shift->total_credit_sales = $sales->sum('due');

            // 4. حساب الأرباح (Profit) بدقة الوحدات
            $totalRevenue = 0;
            $totalCost = 0;

            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    // 1. الإيراد (سعر البيع الفعلي * الكمية)
                    $totalRevenue += ($item->price * $item->quantity);
                    
                    // 2. التكلفة (Cost)
                    $unitCost = 0;

                    // أ) محاولة جلب التكلفة من الوحدة المباعة مباشرة (إذا كانت وحدة إضافية مثل الطبق)
                    if ($item->unit && $item->unit->cost_price > 0) {
                        $unitCost = $item->unit->cost_price;
                    } 
                    // ب) إذا لم يكن للوحدة تكلفة خاصة، أو كانت الوحدة الأساسية، نأخذ تكلفة المنتج الأم
                    elseif ($item->product) {
                        // إذا كانت الوحدة مباعة ولها معامل تحويل (مثلاً كرتون فيه 12 حبة)، نضرب تكلفة الحبة في المعامل
                        $factor = ($item->unit) ? $item->unit->conversion_factor : 1;
                        $unitCost = $item->product->last_cost_price * $factor;
                    }

                    $totalCost += ($unitCost * $item->quantity);
                }
            }
            
            $shift->net_profit = $totalRevenue - $totalCost;
// 🟢 إضافة حساب نسبة الربح
            $shift->profit_percentage = 0;
            if ($totalRevenue > 0) {
                $shift->profit_percentage = ($shift->net_profit / $totalRevenue) * 100;
            }
            // 5. العجز والزيادة (للكاش فقط)
            // المتوقع = بداية الصندوق + مبيعات الكاش
            $expected = $shift->start_cash + $shift->total_cash_sales;
            
            if ($shift->status == 'closed') {
                $shift->difference = $shift->end_cash - $expected; // سالب = عجز، موجب = زيادة
            } else {
                $shift->difference = 0; // الصندوق مفتوح لا يمكن الحكم
            }

            return $shift;
        });

        // قائمة الموظفين للفلتر
        $users = \App\Models\User::where('store_id', $storeId)->get();

        return view('store_owner.reports.shifts', compact('shifts', 'users'));
    }
}