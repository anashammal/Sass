<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Mail; // Unused
// use Illuminate\Support\Facades\Http; // Unused
use Illuminate\Support\Facades\Log;  // لتسجيل الأخطاء
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ArabicTextService;
use App\Models\Currency;
use App\Models\StoreCurrency;
use App\Services\ExchangeRateService;



class PurchaseController extends Controller
{

    // ... (index, create, edit, show, searchSuppliers, searchProducts) ...
    // سأضع لك الدوال التي تحتاج تعديل جذري فقط (store, destroy) لتختصر الوقت
    // لكن الأفضل نسخ الملف كاملاً لضمان عدم نسيان شيء.

    public function index(Request $request)
    {
        $user = Auth::user();
        $storeId = $user->store->id;
        $store = clone $user->store;
        
        $baseCurrency = $store->baseCurrency;
        
        $acceptedCurrencies = $store->acceptedCurrencies()->get();
        if ($acceptedCurrencies->isEmpty()) {
            $acceptedCurrencies = \App\Models\Currency::all();
        }
        if ($baseCurrency && !$acceptedCurrencies->contains('id', $baseCurrency->id)) {
            $acceptedCurrencies->push($baseCurrency);
        }
        $currencies = $acceptedCurrencies->unique('id')->values();

        $query = Purchase::where('store_id', $storeId)->with(['supplier', 'currency']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('invoice_number', 'like', "%$term%")
                  ->orWhere('notes', 'like', "%$term%")
                  ->orWhereHas('supplier', function($qSup) use ($term) {
                      $qSup->where('contact_name', 'like', "%$term%")
                           ->orWhere('company_name', 'like', "%$term%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        $sortField = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('order_by', 'desc');
        
        if (in_array($sortField, ['grand_total', 'invoice_date', 'created_at'])) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $totals = [
            'count' => (clone $query)->count(),
            'sum_total' => (clone $query)->get()->sum(function($p) { return $p->grand_total_in_base_currency; }),
            'sum_paid' => (clone $query)->get()->sum('paid_amount'),
            'sum_due' => (clone $query)->get()->sum(function($p) { return $p->remaining_amount_in_base_currency; }),
        ];

        // تفصيل حسب العملة (المتوسط المرجح لأسعار الصرف)
        $purchasesForBreakdown = (clone $query)->get();
        $currencyBreakdown = $purchasesForBreakdown->groupBy(function($purchase) use ($baseCurrency) {
                return $purchase->currency_id ?: optional($baseCurrency)->id;
            })
            ->map(function($group, $cid) use ($baseCurrency) {
                $currency = \App\Models\Currency::find($cid) ?? $baseCurrency;
                if (!$currency) return null;

                $totalForeign = (float) $group->sum('grand_total');
                $totalBase = (float) $group->sum(function($p) {
                    return $p->grand_total_in_base_currency;
                });

                // Weighted Average Rate = Total Base / Total Foreign
                $weightedRate = $totalForeign > 0 ? ($totalBase / $totalForeign) : 1;

                return [
                    'currency'      => $currency,
                    'total_amount'  => $totalForeign,
                    'exchange_rate' => $weightedRate,
                    'in_base'       => $totalBase
                ];
            })->filter()->values();

        $purchases = $query->paginate(10)->withQueryString();
        $suppliers = Contact::where('store_id', $storeId)->whereIn('type', ['supplier', 'both'])->get();

        return view('store_owner.purchases.index', compact('purchases', 'suppliers', 'totals', 'baseCurrency', 'currencies', 'currencyBreakdown'));
    }

    public function pdfReport(Request $request)
    {
        $user = Auth::user();
        $store = $user->store;
        $storeId = $store->id;

        // زيادة الزمن والذاكرة للتقارير الكبيرة
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $query = Purchase::where('store_id', $storeId)->with(['supplier', 'items.product', 'items.unit']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('invoice_number', 'like', "%$term%")
                  ->orWhere('notes', 'like', "%$term%")
                  ->orWhereHas('supplier', function($qSup) use ($term) {
                      $qSup->where('contact_name', 'like', "%$term%")
                           ->orWhere('company_name', 'like', "%$term%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $sortField = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('order_by', 'desc');
        
        if (in_array($sortField, ['grand_total', 'invoice_date', 'created_at'])) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $purchases = $query->get();
        
        $totals = [
            'count' => $purchases->count(),
            'sum_total' => $purchases->sum(function($p){ return $p->grand_total_in_base_currency; }),
            'sum_paid' => $purchases->sum('paid_amount'),
            'sum_due' => $purchases->sum(function($p){ return $p->remaining_amount_in_base_currency; }),
        ];

        // استخدام خدمة معالجة النص العربي
        $arabicService = new \App\Services\ArabicTextService();
        
        $pdf = Pdf::loadView('store_owner.purchases.pdf_report', compact('purchases', 'store', 'totals', 'arabicService'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'isFontSubsettingEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);
        
        if ($request->get('output') == 'url') {
            $filename = 'purchase_report_' . date('Ymd_His') . '_' . uniqid() . '.pdf';
            $path = public_path('temp_reports');
            if (!file_exists($path)) mkdir($path, 0777, true);
            $pdf->save($path . '/' . $filename);
            return response()->json([
                'url' => asset('temp_reports/' . $filename),
                'filename' => $filename
            ]);
        }

        return $pdf->download('purchase_report.pdf');
    }

    public function interactiveReport(Request $request)
    {
        $user = Auth::user();
        $store = $user->store;
        $storeId = $store->id;

        $query = Purchase::where('store_id', $storeId)->with(['supplier', 'items.product', 'items.unit']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('invoice_number', 'like', "%$term%")
                  ->orWhere('notes', 'like', "%$term%")
                  ->orWhereHas('supplier', function($qSup) use ($term) {
                      $qSup->where('contact_name', 'like', "%$term%")
                           ->orWhere('company_name', 'like', "%$term%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $sortField = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('order_by', 'desc');
        
        if (in_array($sortField, ['grand_total', 'invoice_date', 'created_at'])) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $purchases = $query->get();
        
        $totals = [
            'count' => $purchases->count(),
            'sum_total' => $purchases->sum(function($p){ return $p->grand_total_in_base_currency; }),
            'sum_paid' => $purchases->sum('paid_amount'),
            'sum_due' => $purchases->sum(function($p){ return $p->remaining_amount_in_base_currency; }),
        ];

        return view('store_owner.purchases.interactive_report', compact('purchases', 'store', 'totals'));
    }

   public function create() 
    { 
        $store = Auth::user()->store; 
        $suppliers = Contact::where('store_id', $store->id)->whereIn('type', ['supplier', 'both'])->get();
        $taxRates = explode(',', $store->tax_rates ?? '0,15');

        $baseCurrency = $store->baseCurrency;
        $acceptedCurrencies = $store->acceptedCurrencies()->get();
        // إذا لم تكن هناك عملات مقبولة, نجلب كل العملات من الجدول
        if ($acceptedCurrencies->isEmpty()) {
            $acceptedCurrencies = \App\Models\Currency::all();
        }
        if ($baseCurrency && !$acceptedCurrencies->contains('id', $baseCurrency->id)) {
            $acceptedCurrencies->push($baseCurrency);
        }
        $currencies = $acceptedCurrencies->unique('id')->values();

        $lastPurchase = Purchase::where('store_id', $store->id)->latest()->first();
        $nextId = $lastPurchase ? ($lastPurchase->id + 1) : 1;
        $nextInvoiceNumber = 'PUR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        // 🔥 تعديل التوقيت: نرسل الوقت حسب المنطقة الزمنية للمتجر 🔥
        $timezone = $store->timezone ?? config('app.timezone');
        $currentDate = now()->setTimezone($timezone)->format('Y-m-d\TH:i'); 

        // تحميل أسعار الصرف مسبقاً (كمعامل ضرب: كم ليرة مقابل 1 من العملة الأجنبية)
        $currenciesData = [];
        if ($baseCurrency) {
            $exchangeService = app(ExchangeRateService::class);
            foreach ($currencies as $cur) {
                if ($cur->id === $baseCurrency->id) continue;
                // إعطاء الأولوية للسعر المخصص في المتجر، وإلا الجلب من الخدمة
                $rate = $cur->pivot->custom_rate ?? ($exchangeService->getExchangeRate($cur->code, $baseCurrency->code) ?? 1);
                $currenciesData[] = [
                    'id'            => $cur->id,
                    'code'          => $cur->code,
                    'symbol'        => $cur->symbol ?? $cur->code,
                    'exchange_rate' => $rate,
                    'is_base'       => false,
                ];
            }
        }

        return view('store_owner.purchases.create', compact('suppliers', 'taxRates', 'nextInvoiceNumber', 'currentDate', 'baseCurrency', 'currencies', 'currenciesData')); 
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('save_type') === 'draft';

        if (!$isDraft) {
            $request->validate([
                'supplier_id' => 'required|exists:contacts,id',
                'invoice_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.expiry_date' => 'required|date',
            ], [
                'supplier_id.required' => 'يجب اختيار المورد.',
                'items.*.expiry_date.required' => 'تاريخ الانتهاء مطلوب لجميع المنتجات.',
            ]);
        }

        try {
            DB::beginTransaction();
            $user = Auth::user();
            $store = $user->store;
            $storeId = $store->id;
            
            $baseCurrency = $store->baseCurrency;
            $currencyId = $request->input('currency_id', optional($baseCurrency)->id);
            
            // 💱 استخدام سعر الصرف المرسل من الواجهة إذا وجد (المؤكد من المستخدم)
            if ($request->has('exchange_rate') && (float)$request->exchange_rate > 0) {
                $exchangeRate = (float)$request->exchange_rate;
            } else {
                $exchangeRate = 1;
                // Fallback: جلب سعر الصرف إذا كانت العملة مختلفة عن الأساسية
                if ($baseCurrency && $currencyId && $currencyId != $baseCurrency->id) {
                    $targetCurrency = Currency::find($currencyId);
                    if ($targetCurrency) {
                        $exchangeRateService = app(ExchangeRateService::class);
                        // جلب السعر المخصص إذا وجد
                        $storeCurrency = \App\Models\StoreCurrency::where('store_id', $storeId)
                            ->where('currency_id', $currencyId)
                            ->first();
                        $rate = $storeCurrency->custom_rate ?? ($exchangeRateService->getExchangeRate($targetCurrency->code, $baseCurrency->code) ?? 1);
                        $exchangeRate = $rate ?? 1;
                    }
                }
            }

            $purchase = null;
            // التحقق من وجود الفاتورة للتعديل
            if ($request->has('purchase_id') && $request->purchase_id) {
                $purchase = Purchase::find($request->purchase_id);
            }

            // إذا كان تعديل، يجب عكس تأثير الفاتورة القديمة على المخزون والرصيد
            if ($purchase) {
                if ($purchase->status == 'approved') {
                    // عكس المخزون
                    foreach ($purchase->items as $oldItem) {
                        $prod = Product::find($oldItem->product_id);
                        if($prod) $prod->decrement('current_stock', $oldItem->quantity_in_base_unit);
                    }
                    // عكس رصيد المورد (نطرح الدين القديم بالعملة الأساسية)
                    $oldSupplier = Contact::find($purchase->supplier_id);
                    if ($oldSupplier) {
                        $oldDebt = $purchase->remaining_amount_in_base_currency;
                        $oldSupplier->decrement('balance', $oldDebt);
                    }
                }
                $purchase->items()->delete();
            } else {
                $purchase = new Purchase();
            }

            $attachmentPath = $purchase->attachment;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('purchases_attachments', 'public');
            }

            $purchase->fill([
                'store_id' => $storeId,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'currency_id' => $currencyId,
                'exchange_rate' => $exchangeRate,
                'notes' => $request->notes,
                'attachment' => $attachmentPath,
                'payment_status' => 'unpaid', // سيتم تحديثها بالأسفل
                'payment_method' => $request->payments[0]['method'] ?? 'cash',
                'status' => $isDraft ? 'draft' : 'approved',
            ]);
            $purchase->save();

            $subTotal = 0;

            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $product = Product::find($itemData['product_id'] ?? null);
                    // Fix: Check if unit_id exists, otherwise try to use product's base unit or default
                    $unitId = $itemData['unit_id'] ?? ($product ? $product->base_unit_id : null);
                    $mainUnit = ProductUnit::find($unitId); 
                    
                    if (!$product || !$mainUnit) {
                        // Log::warning("Skipping item in purchase due to missing product or unit", ['data' => $itemData]);
                        continue;
                    }

                    $quantity = (float)($itemData['quantity'] ?? 0);
                    $unitPrice = (float)($itemData['unit_price'] ?? 0); 
                    
                    $lineTotal = $quantity * $unitPrice;
                    $subTotal += $lineTotal;

                    // حساب المعامل الذكي
                    $baseUnit = \App\Models\ProductUnit::where('product_id', $product->id)
                        ->orderBy('cost_price', 'asc')
                        ->first();

                    $baseCost = ($baseUnit && $baseUnit->cost_price > 0) ? $baseUnit->cost_price : 1;
                    $currentUnitCost = ($mainUnit->cost_price > 0) ? $mainUnit->cost_price : 0;

                    if ($currentUnitCost > 0) {
                        $smartFactor = $currentUnitCost / $baseCost;
                        $smartFactor = round($smartFactor); 
                        if ($smartFactor < 1) $smartFactor = 1;
                    } else {
                        $smartFactor = $mainUnit->conversion_factor ?? 1;
                    }

                    $conversionFactor = $smartFactor; 
                    $qtyInBase = $quantity * $conversionFactor; 
                    $costPerBase = ($conversionFactor > 0) ? ($unitPrice / $conversionFactor) : 0;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'product_unit_id' => $mainUnit->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_cost' => $lineTotal,
                        'quantity_in_base_unit' => $qtyInBase,
                        'cost_per_base_unit' => $costPerBase,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'alert_days'  => (isset($itemData['alert_days']) && $itemData['alert_days'] > 0) ? $itemData['alert_days'] : 10,
                        // ✅ إضافة الحقول التاريخية الجديدة
                        'selling_price' => (float)($itemData['selling_price'] ?? 0),
                        'discount' => (float)($itemData['discount'] ?? 0),
                        'discount_type' => $itemData['discount_type'] ?? 'fixed',
                        'tax_percent' => (float)($itemData['tax'] ?? 0),
                    ]);

                    // تحديث أسعار الوحدات المرتبطة
                    if (isset($itemData['related_updates']) && is_array($itemData['related_updates'])) {
                        foreach ($itemData['related_updates'] as $uId => $updateData) {
                            $relatedUnit = \App\Models\ProductUnit::find($uId);
                            if ($relatedUnit) {
                                // حساب سعر البيع المحول لعملة الوحدة الأصلية
                                $sellInInvoice = (float)($updateData['selling_price'] ?? $relatedUnit->selling_price);
                                
                                // ✅ بما أن المستخدم أدخل سعر المبيع الجديد في الفاتورة، يُفترض أنه بذات عملة الفاتورة
                                $finalSell = $sellInInvoice;

                                $unitFields = [
                                    'purchase_price'             => $updateData['price'],
                                    'cost_price'                 => $updateData['price'],
                                    'selling_price'              => $finalSell,
                                    'profit_percent'             => $updateData['profit_percent'] ?? $relatedUnit->profit_percent,
                                    // ✅ تحديث عملة الشراء والمبيع لتصبح هي عملة الفاتورة مع معدل الصرف الجديد
                                    'purchase_price_currency_id' => $currencyId,
                                    'sell_price_currency_id'     => $currencyId,
                                    'purchase_exchange_rate'     => $exchangeRate,
                                    'sell_exchange_rate'         => $exchangeRate,
                                ];
                                $relatedUnit->update($unitFields);
                            }
                        }
                    }

                    if (!$isDraft) {
                        // 1. تحديث المخزون الكلي
                        $product->increment('current_stock', $qtyInBase);
                        
                        if(isset($itemData['tax'])) {
                            $product->tax_percent = $itemData['tax'];
                            $product->save();
                        }
                        
                        // 2. تحديث أسعار الوحدة المختارة
                        // ✅ بما أن المستخدم أدخل سعر المبيع الجديد في الفاتورة، يُفترض أنه بذات عملة الفاتورة
                        $sellInInvoiceMain = (float)($itemData['selling_price'] ?? $mainUnit->selling_price);
                        $finalSellMain = $sellInInvoiceMain;

                        $mainUpdateFields = [
                            'purchase_price'             => $unitPrice,
                            'cost_price'                 => $unitPrice,
                            'selling_price'              => $finalSellMain,
                            'profit_percent'             => $itemData['profit_percent'] ?? $mainUnit->profit_percent,
                            // ✅ دائماً نُحدِّث عملة الشراء والمبيع وسعر الصرف بعملة الفاتورة الجديدة
                            'purchase_price_currency_id' => $currencyId,
                            'sell_price_currency_id'     => $currencyId,
                            'purchase_exchange_rate'     => $exchangeRate,
                            'sell_exchange_rate'         => $exchangeRate,
                        ];
                        $mainUnit->update($mainUpdateFields);

                        // 3. إنشاء الدفعة (Batch)
                        \App\Models\ProductBatch::create([
                            'product_id'  => $product->id,
                            'quantity'    => $qtyInBase,
                            'cost_price'  => $costPerBase,
                            'expiry_date' => $itemData['expiry_date'] ?? null,
                            'alert_days'  => (isset($itemData['alert_days']) && $itemData['alert_days'] > 0) ? $itemData['alert_days'] : 10,
                        ]);
                    }
                }
            }

            $totalPaid = 0; // المجموع بالعملة الأساسية للمتجر
            if ($request->has('payments')) {
                foreach ($request->payments as $payment) {
                    $amt = (float) ($payment['amount'] ?? 0);
                    $payRate = (float) ($payment['exchange_rate'] ?? 1);
                    $payCurrencyId = $payment['currency_id'] ?? null; // Define payCurrencyId
                    $isBaseCurr = empty($payCurrencyId) 
                        || $payCurrencyId == optional($baseCurrency)->id;
                    if ($isBaseCurr) {
                        $totalPaid += $amt;
                    } else {
                        // Foreign: Base = Foreign * Rate (e.g. 10 USD * 33 = 330 TRY)
                        $totalPaid += $amt * $payRate;
                    }
                }
            }
            $discount = (float) ($request->discount ?? 0);
            $grandTotal = $subTotal - $discount; // العملة الأصلية للفاتورة (مثلاً SAR)
            
            // تحويل الإجمالي للعملة الأساسية (TRY) للمقارنة والحسابات المالية
            $grandTotalInBase = $grandTotal * $exchangeRate;

            $payStatus = 'unpaid';
            if (!$isDraft) {
                // المقارنة الآن صحيحة: TRY vs TRY
                $payStatus = ($totalPaid >= $grandTotalInBase - 0.01) ? 'paid' : (($totalPaid > 0.01) ? 'partial' : 'unpaid');
                
                // تحديث رصيد المورد (بالعملة الأساسية للمتجر)
                $supplier = Contact::find($request->supplier_id);
                if ($supplier) {
                    $debtAmount = $grandTotalInBase - $totalPaid; // كلاهما بالعملة الأساسية (TRY)
                    if (abs($debtAmount) > 0.001) {
                        // استخدام increment مع القيمة (قد تكون سالبة في حال دفع زيادة)
                        $supplier->increment('balance', $debtAmount);
                    }
                }
                
                // إضافة الدفعات (إن وجدت) — كل دفعة بعملتها الخاصة
                if ($request->has('payments') && $totalPaid > 0) {
                    foreach ($request->payments as $payment) {
                        $amt = (float)($payment['amount'] ?? 0);
                        if ($amt <= 0) continue;
                        $paymentCurrencyId = $payment['currency_id'] ?? optional($baseCurrency)->id;
                        $paymentRate = (float)($payment['exchange_rate'] ?? 1);
                        $isBasePayment = empty($paymentCurrencyId) 
                            || $paymentCurrencyId == optional($baseCurrency)->id;
                        // المبلغ بالعملة الأساسية لهذه الدفعة
                        $amtInBase = $isBasePayment ? $amt : ($amt * $paymentRate);
                        \App\Models\Payment::create([
                            'store_id'       => $store->id,
                            'purchase_id'    => $purchase->id,
                            'contact_id'     => $request->supplier_id,
                            'amount'         => $amtInBase,
                            'method'         => $payment['method'] ?? 'cash',
                            'payment_date'   => $request->invoice_date,
                            'currency_id'    => $paymentCurrencyId ?: optional($baseCurrency)->id,
                            'exchange_rate'  => $paymentRate ?: 1,
                            'amount_in_foreign_currency' => $amt,
                        ]);
                    }
                }
            }

            $purchase->update([
                'sub_total' => $subTotal,
                'discount_amount' => $discount,
                'grand_total' => $grandTotal,
                'paid_amount' => $totalPaid,
                'payment_status' => $payStatus,
            ]);

            DB::commit();

            // 🔥 تحديث تكلفة الوجبات المتأثرة بتغير أسعار المكونات (للمطاعم) - تم النقل للخلفية
            if (!$isDraft && $store->type == 'restaurant') {
                $ingredientIds = array_filter(array_column($request->items, 'product_id'));
                if (!empty($ingredientIds)) {
                    \App\Jobs\RecalculateMealCostsJob::dispatch($ingredientIds);
                }
            }

            $pdfData = ['success' => false, 'url' => null, 'filename' => null];
            // if (!$isDraft) {
            //    $pdfData = $this->generateInvoicePdf($purchase); // تم الطي لزيادة السرعة
            // }

            // ============================================================
            // 🔥 منطقة إشعارات المشتريات (المتجر) - تم النقل للخلفية 🔥
            // ============================================================
            if (!$isDraft) {
                try {
                     \App\Jobs\ProcessPurchaseNotifications::dispatch($purchase->id);
                     // Log::info("Purchase Notification Job Dispatched: " . $purchase->id);
                } catch (\Exception $e) {
                    Log::error("Purchase Notification Job Failed: " . $e->getMessage());
                }
            }

            if ($request->ajax()) {
                $whatsappData = null;
                if (!$isDraft && isset($store) && $store->whatsapp_auto_prompt && $purchase->supplier && $purchase->supplier->phone) {
                    $purchase->load(['items.product.baseUnit', 'items.unit']);
                    $itemsLines = [];
                    foreach($purchase->items as $item) {
                        $uName = $item->unit->unit_name ?? ($item->product->baseUnit->unit_name ?? 'قطعة');
                        $itemsLines[] = "• " . ($item->product->name ?? 'منتج') . " ({$item->quantity} {$uName})";
                    }
                    
                    $baseCurrency = \App\Models\Currency::find($store->base_currency_id);
                    $foreignCurrency = $purchase->currency;
                    $exchangeRate = (float)($purchase->exchange_rate ?: 1);
                    $hasForeign = ($foreignCurrency && $baseCurrency && $foreignCurrency->id != $baseCurrency->id && $exchangeRate != 1);

                    $msgBody = "*أمر شراء / فاتورة مشتريات #{$purchase->invoice_number}*\n";
                    $msgBody .= "التاريخ: " . ($purchase->invoice_date ? $purchase->invoice_date->format('Y-m-d') : now()->format('Y-m-d')) . "\n";
                    $msgBody .= "المورد: " . ($purchase->supplier->contact_name ?? $purchase->supplier->company_name) . "\n";
                    $msgBody .= "--------------------------\n";
                    $msgBody .= implode("\n", $itemsLines) . "\n";
                    $msgBody .= "--------------------------\n";
                    
                    if ($hasForeign) {
                        $msgBody .= "*الإجمالي:* " . number_format($purchase->grand_total, 2) . " {$foreignCurrency->code}\n";
                        $msgBody .= "*المعادل:* " . number_format($purchase->grand_total_in_base_currency, 2) . " " . optional($baseCurrency)->code . "\n";
                        $msgBody .= "*سعر الصرف:* " . (1* $exchangeRate == (int)($exchangeRate) ? number_format($exchangeRate, 0) : number_format($exchangeRate, 2)) . "\n";
                    } else {
                        $msgBody .= "*الإجمالي:* " . number_format($purchase->grand_total, 2) . " " . ($foreignCurrency ? $foreignCurrency->code : optional($baseCurrency)->code) . "\n";
                    }

                    $due = $purchase->grand_total_in_base_currency - $purchase->paid_amount;
                    if($due > 0.01) $msgBody .= "*المتبقي (آجل):* " . number_format($due, 2) . " " . optional($baseCurrency)->code . "\n";
                    
                    $msgBody .= "عن متجر: *" . $store->name . "*";

                    $whatsappData = [
                        'phone' => $purchase->supplier->phone,
                        'message' => $msgBody,
                        'pdf_url' => $pdfData['url'] ?? null,
                        'pdf_filename' => $pdfData['filename'] ?? null
                    ];
                }

                return response()->json([
                    'success' => true,
                    'id' => $purchase->id,
                    'message' => $isDraft ? 'تم حفظ المسودة' : 'تم حفظ الفاتورة',
                    'whatsapp_data' => $whatsappData,
                    'supplier_email' => ($purchase->supplier ? $purchase->supplier->email : null),
                    'invoice_no' => $purchase->invoice_number,
                    'pdf_url' => $pdfData['url'] ?? null,
                    'pdf_filename' => $pdfData['filename'] ?? null
                ]);
            }

            return redirect()->route('store.purchases.index')->with('success', 'تم حفظ الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        // 1. Fetch only necessary relations (removed media from items.product.units to save memory initially)
        $purchase = Purchase::with(['items.product', 'items.unit', 'supplier', 'payments.currency'])->findOrFail($id);
        
        // 2. Prepare Items Array Manually to avoid JSON recursion crash
        $itemsData = [];
        foreach($purchase->items as $item) {
            if(!$item->product) continue;
            
            // Image Logic
            // ✅ Fix: Use the accessor which handles dynamic URLs correctly (Local vs Online)
            $img = $item->product->image_url;
            if($item->unit && $item->unit->hasMedia('unit_images')) {
                $img = $item->unit->image;
            }

            // Map Units Manually
            $units = $item->product->units->map(function($u) {
                 return [
                    'id' => $u->id,
                    'unit_name' => $u->unit_name,
                    'is_base_unit' => $u->is_base_unit,
                    'conversion_factor' => $u->conversion_factor,
                    'is_purchase' => $u->is_purchase,
                    'barcode' => $u->barcode, 
                    'cost_price' => $u->cost_price,
                    'purchase_price' => $u->purchase_price,
                    'selling_price' => $u->selling_price,
                    'profit_percent' => $u->profit_percent,
                    // ✅ Add missing currency fields for units
                    'purchase_price_currency_id' => $u->purchase_price_currency_id ?? $u->purchase_currency_id,
                    'purchase_exchange_rate' => $u->purchase_exchange_rate ?? $u->store_custom_purchase_rate,
                    'sell_price_currency_id' => $u->sell_price_currency_id ?? $u->sell_currency_id,
                    'sell_exchange_rate' => $u->sell_exchange_rate ?? $u->store_custom_sell_rate,
                    'image_url' => $u->image // accessor
                 ];
            });

            $itemsData[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_unit_id' => $item->product_unit_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_cost' => $item->total_cost,
                'expiry_date' => $item->expiry_date,
                'alert_days' => $item->alert_days,
                // ✅ الحقول التاريخية المخزنة في الفاتورة (نتركها كما هي لتتمكن JS من التعامل مع الفواتير القديمة)
                'selling_price' => $item->selling_price,
                'discount' => $item->discount ?? 0,
                'discount_type' => $item->discount_type ?? 'fixed',
                'tax_percent' => $item->tax_percent ?? 0,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'image_url' => $img,
                    'scanned_unit_id' => $item->product_unit_id,
                    'units' => $units
                ]
            ];
        }

        $store = Auth::user()->store;
        $suppliers = Contact::where('store_id', $store->id)->whereIn('type', ['supplier', 'both'])->get();
        $taxRates = explode(',', $store->tax_rates ?? '0,15');

        $baseCurrency = $store->baseCurrency;
        $acceptedCurrencies = $store->acceptedCurrencies()->get();
        // إذا لم تكن هناك عملات مقبولة, نجلب كل العملات من الجدول
        if ($acceptedCurrencies->isEmpty()) {
            $acceptedCurrencies = \App\Models\Currency::all();
        }
        if ($baseCurrency && !$acceptedCurrencies->contains('id', $baseCurrency->id)) {
            $acceptedCurrencies->push($baseCurrency);
        }
        $currencies = $acceptedCurrencies->unique('id')->values();
        
        // جلب بيانات العملات وسعر الصرف الحالي (مع السعر المخصص)
        $currenciesData = [];
        if($baseCurrency) {
            $exchangeService = app(ExchangeRateService::class);
            foreach($currencies as $cur) {
                if($cur->id === $baseCurrency->id) continue;
                $rate = $cur->pivot->custom_rate ?? ($exchangeService->getExchangeRate($cur->code, $baseCurrency->code) ?? 1);
                $currenciesData[] = [
                    'id'            => $cur->id,
                    'code'          => $cur->code,
                    'symbol'        => $cur->symbol ?? $cur->code,
                    'exchange_rate' => (float)$rate,
                    'is_base'       => false,
                ];
            }
        }

        return view('store_owner.purchases.edit', compact('purchase', 'suppliers', 'taxRates', 'itemsData', 'baseCurrency', 'currencies', 'currenciesData'));
    }

    public function update(Request $request, $id)
    {
        $request->merge(['purchase_id' => $id]);
        return $this->store($request);
    }

    public function destroy($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            DB::beginTransaction();

            if ($purchase->status == 'approved') {
                // عكس المخزون
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('current_stock', $item->quantity_in_base_unit);
                    }
                }
                
                // 🟢🔴 عكس رصيد المورد عند الحذف (بالعملة الأساسية)
                $supplier = Contact::find($purchase->supplier_id);
                if ($supplier) {
                    $debt = $purchase->remaining_amount_in_base_currency;
                    $supplier->decrement('balance', $debt);
                }
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();
            return redirect()->route('store.purchases.index')->with('success', 'تم حذف الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $purchase = Purchase::with(['items.product', 'items.unit', 'supplier', 'currency'])->findOrFail($id);

        // هذا هو السطر السحري: إذا كان الطلب AJAX (من النافذة)
        if ($request->ajax()) {
            // نرسل ملف العرض الجزئي (الذي سننشئه في الخطوة 2)
            return view('store_owner.purchases.partials.show_modal', compact('purchase'))->render();
        }

        // إذا كان دخول عادي من الرابط
        return view('store_owner.purchases.show', compact('purchase'));
    }

    public function searchSuppliers(Request $request) {
        $term = $request->term;
        $suppliers = Contact::where('store_id', Auth::user()->store->id)
            ->where(function($q) {
                $q->where('type', 'like', '%supplier%')
                  ->orWhere('type', 'like', '%مورد%')
                  ->orWhere('type', 'both');
            })
            ->where(function($q) use ($term) {
                $q->where('contact_name', 'like', "%$term%")
                  ->orWhere('company_name', 'like', "%$term%")
                  ->orWhere('phone', 'like', "%$term%");
            })
            ->take(10)->get();
        
        $suppliers->transform(function($s) {
            $s->current_balance = $s->balance ?? 0;
            return $s;
        });

        return response()->json($suppliers);
    }

    public function searchProducts(Request $request) {
        try {
            $term = $request->term;
            $store = Auth::user()->store;
            $storeId = $store->id;
            
            // جلب أسعار الصرف المخصصة للمتجر لاستخدامها كافتراضي إذا لم يوجد سعر خاص بالوحدة
            $storeCustomRates = DB::table('store_currencies')
                ->where('store_id', $storeId)
                ->pluck('custom_rate', 'currency_id');

            // Limit the select fields to reduce memory usage and avoid accidental blob loading
            $products = Product::where('store_id', $storeId)
                ->where(function($q) use ($term) {
                    $q->where('name', 'like', "%$term%")
                      ->orWhere('sku', 'like', "%$term%") 
                      ->orWhereHas('units', function($q2) use ($term) {
                          $q2->where('barcode', 'like', "%$term%");
                      });
                })
                ->whereIn('product_type', ['standard', 'ingredient', 'meal'])
                ->whereHas('units', function($q) {
                    $q->where('is_purchase', true);
                })
                ->with(['units' => function($q) {
                    $q->select('id','product_id','unit_name','barcode','cost_price','selling_price','conversion_factor','is_base_unit','is_purchase','profit_percent','purchase_price_currency_id','sell_price_currency_id', 'purchase_exchange_rate', 'sell_exchange_rate')
                      ->with(['purchaseCurrency', 'sellCurrency']);
                }]) 
                ->take(20)
                ->get();
            
            // Manual mapping to ensure no circular references or heavy objects
            $results = $products->map(function ($product) use ($term, $storeCustomRates) {
                // Find matched unit ID if searching by barcode
                $matchedUnit = $product->units->firstWhere('barcode', $term);
                
                // Construct a safe, simple object
                return [
                    'id' => $product->id,
                    'text' => $product->name . ' (' . $product->sku . ')',
                    'name' => $product->name,
                    'name_ar' => $product->name,
                    'sku' => $product->sku,
                    'main_image' => $product->image_url,
                    'scanned_unit_id' => $matchedUnit ? $matchedUnit->id : null,
                    'units' => $product->units->map(function($unit) use ($storeCustomRates) {
                        return [
                            'id' => $unit->id,
                            'unit_name' => $unit->unit_name,
                            'barcode' => $unit->barcode,
                            'cost_price' => $unit->cost_price,
                            'sale_price' => $unit->selling_price, 
                            'conversion_factor' => $unit->conversion_factor,
                            'is_base_unit' => $unit->is_base_unit,
                            'is_purchase' => $unit->is_purchase,
                            'profit_percent' => $unit->profit_percent,
                            'image_url' => (strpos($unit->image, 'default-product.png') !== false) ? null : $unit->image,
                            'purchase_currency_id' => $unit->purchase_price_currency_id,
                            'purchase_currency_code' => optional($unit->purchaseCurrency)->code,
                            'purchase_currency_symbol' => optional($unit->purchaseCurrency)->symbol,
                            'purchase_exchange_rate' => $unit->purchase_exchange_rate,
                            'store_custom_purchase_rate' => $storeCustomRates[$unit->purchase_price_currency_id] ?? null,
                            'sell_currency_id' => $unit->sell_price_currency_id,
                            'sell_currency_code' => optional($unit->sellCurrency)->code,
                            'sell_currency_symbol' => optional($unit->sellCurrency)->symbol,
                            'sell_exchange_rate' => $unit->sell_exchange_rate,
                            'store_custom_sell_rate' => $storeCustomRates[$unit->sell_price_currency_id] ?? null,
                        ];
                    })
                ];
            });

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error("Search Products Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getProductHistory($id)
    {
        try {
            $storeId = Auth::user()->store->id;
            
            $history = \App\Models\PurchaseItem::where('product_id', $id)
                ->whereHas('purchase', function($q) use ($storeId) {
                    $q->where('store_id', $storeId)
                      ->where('status', 'approved'); 
                })
                ->with(['purchase.supplier', 'unit'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'date' => $item->purchase->invoice_date ? $item->purchase->invoice_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
                        'supplier' => $item->purchase->supplier ? ($item->purchase->supplier->contact_name ?? $item->purchase->supplier->company_name) : 'مورد عام',
                        'price' => (float)$item->unit_price,
                        'unit' => $item->unit ? $item->unit->unit_name : '---',
                        'qty' => (float)$item->quantity
                    ];
                });

            return response()->json($history);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to generate PDF for the purchase invoice
     */
    private function generateInvoicePdf(Purchase $purchase)
    {
        try {
            $store = $purchase->store;
            $purchase->load(['items.product.units', 'items.unit', 'supplier', 'user']);
            
            // Arabic Text Service
            $arabicService = new ArabicTextService();

            $pdf = Pdf::loadView('store_owner.purchases.invoice_pdf', compact('purchase', 'store', 'arabicService'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);

            $filename = 'purchase_' . $purchase->id . '_' . date('Ymd_His') . '.pdf';
            $path = public_path('temp_reports');
            
            if (!file_exists($path)) {
                @mkdir($path, 0777, true);
            }
            
            // Cleanup old files
            foreach (glob($path . '/*.pdf') as $file) {
                if (filemtime($file) < time() - 3600) { 
                    @unlink($file); 
                }
            }

            $pdf->save($path . '/' . $filename);
            
            return [
                'success' => true,
                'url' => asset('temp_reports/' . $filename),
                'filename' => $filename
            ];

        } catch (\Exception $e) {
            Log::error("Purchase PDF Error: " . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * API: جلب سعر الصرف بين عملتين
     */
    public function getExchangeRateApi(Request $request)
    {
        try {
            $from = strtoupper($request->get('from')); // العملة المصدر (الأساسية)
            $to   = strtoupper($request->get('to'));   // العملة الهدف
            
            if (!$from || !$to || $from === $to) {
                return response()->json(['rate' => 1]);
            }
            
            $service = app(ExchangeRateService::class);
            $ratesData = $service->getRates($from);
            
            if ($ratesData['success'] && isset($ratesData['rates'][$to])) {
                return response()->json(['rate' => $ratesData['rates'][$to]]);
            }
            
            return response()->json(['rate' => 1, 'message' => 'Rate not found']);
        } catch (\Exception $e) {
            return response()->json(['rate' => 1, 'error' => $e->getMessage()]);
        }
    }
}
