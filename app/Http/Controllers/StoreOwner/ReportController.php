<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Sale;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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
                    
                    // 2. التكلفة (FIFO Cost) - ✅ المصدر الأساسي هو القيمة المخزنة
                    if (!is_null($item->cost) && $item->cost > 0) {
                        $totalCost += $item->cost;
                    } else {
                        // fallback: طريقة الحساب القديمة (للفواتير القديمة قبل تفعيل FIFO)
                        $unitCost = 0;

                        // أ) محاولة جلب التكلفة من الوحدة المباعة مباشرة
                        if ($item->unit && $item->unit->cost_price > 0) {
                            $unitCost = $item->unit->cost_price;
                        } 
                        // ب) إذا لم يكن للوحدة تكلفة خاصة، أو كانت الوحدة الأساسية
                        elseif ($item->product) {
                            $factor = ($item->unit) ? $item->unit->conversion_factor : 1;
                            $unitCost = $item->product->last_cost_price * $factor;
                        }

                        $totalCost += ($unitCost * $item->quantity);
                    }
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

        // 🟢 حساب الإجماليات للفترة المحددة (Summary)
        $summary = [
            'revenue' => 0,
            'cost' => 0,
            'expenses' => 0,
            'net_profit' => 0
        ];

        // 1. إجمالي المبيعات والتكلفة (بناءً على الفلاتر)
        $salesQ = Sale::where('store_id', $storeId);
        if ($request->date_from) $salesQ->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $salesQ->whereDate('created_at', '<=', $request->date_to);
        if ($request->user_id) $salesQ->where('user_id', $request->user_id);

        // نحتاج تجميع القيم من التراجم (SaleItems)
        // لتقليل الحمل، يمكننا استخدام Join
        $salesIds = $salesQ->pluck('id');
        
        $summary['revenue'] = \App\Models\SaleItem::whereIn('sale_id', $salesIds)->selectRaw('sum(price * quantity) as total')->value('total') ?? 0;
        $summary['cost']    = \App\Models\SaleItem::whereIn('sale_id', $salesIds)->sum('cost') ?? 0;

        // 2. إجمالي المصاريف (حسب التاريخ فقط، لأن المصاريف عادة عامة للمتجر)
        $expensesQ = \App\Models\Expense::where('store_id', $storeId);
        if ($request->date_from) $expensesQ->whereDate('expense_date', '>=', $request->date_from);
        if ($request->date_to) $expensesQ->whereDate('expense_date', '<=', $request->date_to);
        
        $summary['expenses'] = $expensesQ->sum('amount');

        // 3. صافي الربح
        $summary['gross_profit'] = $summary['revenue'] - $summary['cost'];
        $summary['net_profit'] = $summary['gross_profit'] - $summary['expenses'];


        return view('store_owner.reports.shifts', compact('shifts', 'users', 'summary'));
    }

    public function inventoryLogReport(Request $request)
    {
        $storeId = Auth::user()->store->id;
        $query = \App\Models\InventoryActionLog::where('store_id', $storeId)
                    ->with(['product', 'user', 'batch'])
                    ->latest();

        // 1. الفلاتر
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 2. التنقل (Pagination)
        $perPage = $request->input('per_page', 10);
        if ($perPage == 'all') {
            $logs = $query->get();
        } else {
            $logs = $query->paginate($perPage)->withQueryString();
        }

        $users = \App\Models\User::where('store_id', $storeId)->get();

        if ($request->ajax()) {
            return view('store_owner.reports.partials.inventory_logs_table', compact('logs'))->render();
        }

        return view('store_owner.reports.inventory_logs', compact('logs', 'users'));
    }

    public function inventoryLogPdf(Request $request)
    {
        $user = Auth::user();
        $store = $user->store;
        $storeId = $store->id;
        
        // زيادة الزمن والذاكرة للتقارير الكبيرة
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $query = \App\Models\InventoryActionLog::where('store_id', $storeId)
                    ->with(['product', 'user', 'batch'])
                    ->latest();

        // فلاتر مماثلة للموجودة في العرض
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $arabicService = new \App\Services\ArabicTextService();
        
        $pdf = Pdf::loadView('store_owner.reports.pdf_inventory_logs', compact('logs', 'store', 'arabicService'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'isFontSubsettingEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);
        
        if ($request->get('output') == 'url') {
            $filename = 'inventory_log_' . date('Ymd_His') . '_' . uniqid() . '.pdf';
            $path = public_path('temp_reports');
            if (!file_exists($path)) mkdir($path, 0777, true);
            $pdf->save($path . '/' . $filename);
            return response()->json([
                'url' => asset('temp_reports/' . $filename),
                'filename' => $filename
            ]);
        }

        return $pdf->download('inventory_log_report.pdf');
    }
}