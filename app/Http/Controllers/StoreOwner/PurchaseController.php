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
use Illuminate\Support\Facades\Mail; // هذا هو سبب الخطأ الحالي
use Illuminate\Support\Facades\Http; // ضروري للواتساب
use Illuminate\Support\Facades\Log;  // لتسجيل الأخطاء
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    // ... (index, create, edit, show, searchSuppliers, searchProducts) ...
    // سأضع لك الدوال التي تحتاج تعديل جذري فقط (store, destroy) لتختصر الوقت
    // لكن الأفضل نسخ الملف كاملاً لضمان عدم نسيان شيء.

    public function index(Request $request)
    {
        $user = Auth::user();
        $storeId = $user->store->id;

        $query = Purchase::where('store_id', $storeId)->with('supplier');

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

        $totals = [
            'count' => (clone $query)->count(),
            'sum_total' => (clone $query)->sum('grand_total'),
            'sum_paid' => (clone $query)->sum('paid_amount'),
            'sum_due' => (clone $query)->sum(DB::raw('grand_total - paid_amount')),
        ];

        $purchases = $query->paginate(10)->withQueryString();
        $suppliers = Contact::where('store_id', $storeId)->whereIn('type', ['supplier', 'both'])->get();

        return view('store_owner.purchases.index', compact('purchases', 'suppliers', 'totals'));
    }

    public function pdfReport(Request $request)
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
            'sum_total' => $purchases->sum('grand_total'),
            'sum_paid' => $purchases->sum('paid_amount'),
            'sum_due' => $purchases->sum(function($p){ return $p->grand_total - $p->paid_amount; }),
        ];

        $pdf = Pdf::loadView('store_owner.purchases.pdf_report', compact('purchases', 'store', 'totals'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
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
            'sum_total' => $purchases->sum('grand_total'),
            'sum_paid' => $purchases->sum('paid_amount'),
            'sum_due' => $purchases->sum(function($p){ return $p->grand_total - $p->paid_amount; }),
        ];

        return view('store_owner.purchases.interactive_report', compact('purchases', 'store', 'totals'));
    }

   public function create() 
    { 
        $store = Auth::user()->store; 
        $suppliers = Contact::where('store_id', $store->id)->whereIn('type', ['supplier', 'both'])->get();
        $taxRates = explode(',', $store->tax_rates ?? '0,15');

        $lastPurchase = Purchase::where('store_id', $store->id)->latest()->first();
        $nextId = $lastPurchase ? ($lastPurchase->id + 1) : 1;
        $nextInvoiceNumber = 'PUR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        // 🔥 تعديل التوقيت: نرسل الوقت حسب المنطقة الزمنية للمتجر 🔥
        $timezone = $store->timezone ?? config('app.timezone');
        $currentDate = now()->setTimezone($timezone)->format('Y-m-d\TH:i'); 

        return view('store_owner.purchases.create', compact('suppliers', 'taxRates', 'nextInvoiceNumber', 'currentDate')); 
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
                    // عكس رصيد المورد (نطرح الدين القديم)
                    $oldSupplier = Contact::find($purchase->supplier_id);
                    if ($oldSupplier) {
                        $oldDebt = $purchase->grand_total - $purchase->paid_amount;
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
                    $product = Product::find($itemData['product_id']);
                    $mainUnit = ProductUnit::find($itemData['unit_id']); 
                    
                    if (!$product || !$mainUnit) continue;

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
                    ]);

                    // تحديث أسعار الوحدات المرتبطة
                    if (isset($itemData['related_updates']) && is_array($itemData['related_updates'])) {
                        foreach ($itemData['related_updates'] as $uId => $updateData) {
                            $relatedUnit = \App\Models\ProductUnit::find($uId);
                            if ($relatedUnit) {
                                $relatedUnit->update([
                                    'purchase_price' => $updateData['price'],
                                    'cost_price'       => $updateData['price'],
                                    'selling_price'  => $updateData['selling_price'] ?? $relatedUnit->selling_price,
                                    'profit_percent' => $updateData['profit_percent'] ?? $relatedUnit->profit_percent,
                                ]);
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
                        $mainUnit->update([
                            'purchase_price' => $unitPrice,
                            'cost_price'     => $unitPrice,
                            'selling_price'  => $itemData['selling_price'] ?? $mainUnit->selling_price,
                            'profit_percent' => $itemData['profit_percent'] ?? $mainUnit->profit_percent,
                        ]);

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

            $totalPaid = 0;
            if ($request->has('payments')) {
                foreach ($request->payments as $payment) {
                    $totalPaid += (float) ($payment['amount'] ?? 0);
                }
            }

            $discount = (float) ($request->discount ?? 0);
            $grandTotal = $subTotal - $discount;
            
            $payStatus = 'unpaid';
            if (!$isDraft) {
                $payStatus = ($totalPaid >= $grandTotal) ? 'paid' : (($totalPaid > 0) ? 'partial' : 'unpaid');
                
                // تحديث رصيد المورد
                $supplier = Contact::find($request->supplier_id);
                if ($supplier) {
                    $debtAmount = $grandTotal - $totalPaid;
                    $supplier->increment('balance', $debtAmount);
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

            // ============================================================
            // 🔥 منطقة إشعارات المشتريات (الجديدة والمنفصلة) 🔥
            // ============================================================
            if (!$isDraft) {
                try {
                    $user = Auth::user();
                    $store = $user->store;
                    
                    // المتغيرات المشتركة
                    $netTotal = $grandTotal;
                    $due = $grandTotal - $totalPaid;
                    $isCredit = ($due > 0);
                    // تحميل علاقة المورد لضمان وجود البيانات
                    $purchase->load('supplier');
                    // جلب الاسم (نبحث عن contact_name أو company_name)
                    $sup = $purchase->supplier;
                    $supplierName = $sup ? ($sup->contact_name ?? $sup->company_name ?? 'مورد عام') : 'مورد عام';

                    // ✅ التحقق من تشفير اللغة العربية قبل الإرسال (Clean UTF-8)
                    $supplierName = mb_convert_encoding($supplierName, 'UTF-8', 'UTF-8');

                    // 1. منطق الواتساب (مستقل)
                    if ($store->notify_whatsapp && $store->phone_number && $store->wa_notify_purchases) {
                        $waSend = false;
                        
                        if ($store->wa_purchases_credit_only) {
                            // شرط الدين فقط للواتس
                            if ($isCredit && $due >= $store->wa_purchases_credit_min) $waSend = true;
                        } else {
                            // شرط عام للواتس
                            if ($netTotal >= $store->wa_purchases_min) $waSend = true;
                            if ($isCredit && $due >= $store->wa_purchases_credit_min) $waSend = true;
                        }

                        if ($waSend) {
                            $msg = "🚛 *فاتورة مشتريات جديدة #{$purchase->id}*\n";
                            $msg .= "👤 المورد: {$supplierName}\n";
                            $msg .= "💰 القيمة: " . number_format($netTotal, 2) . "\n";
                            if($isCredit) $msg .= "❗️ آجل (دين): " . number_format($due, 2) . "\n";
                            $msg .= "✍️ بواسطة: {$user->name}";

                            // استخدام timeout قصير جداً لتقليل التعليق
                            Http::timeout(1)->withoutVerifying()->post('https://wa.tech-sys.online/send-message', [
                                'phone' => $store->phone_number,
                                'message' => $msg,
                                'session_id' => 'store_' . $store->id
                            ]);
                        }
                    }

                    // 2. منطق الإيميل (مستقل)
                    if ($store->notify_email && $store->email && $store->email_notify_purchases) {
                        $emailSend = false;

                        if ($store->email_purchases_credit_only) {
                            // شرط الدين فقط للإيميل
                            if ($isCredit && $due >= $store->email_purchases_credit_min) $emailSend = true;
                        } else {
                            // شرط عام للإيميل
                            if ($netTotal >= $store->email_purchases_min) $emailSend = true;
                            if ($isCredit && $due >= $store->email_purchases_credit_min) $emailSend = true;
                        }

                        if ($emailSend) {
                            $emailMsg = "تم تسجيل فاتورة مشتريات جديدة.\n\n";
                            $emailMsg .= "رقم الفاتورة: #{$purchase->id}\n";
                            $emailMsg .= "المورد: {$supplierName}\n";
                            $emailMsg .= "الإجمالي: " . number_format($netTotal, 2) . "\n";
                            $emailMsg .= "المدفوع: " . number_format($totalPaid, 2) . "\n";
                            $emailMsg .= "المتبقي (آجل): " . number_format($due, 2) . "\n";
                            $emailMsg .= "بواسطة: {$user->name}";

                            Mail::raw($emailMsg, function($m) use ($store, $purchase) {
                                $m->to($store->email)->subject("فاتورة شراء #{$purchase->id}");
                            });
                        }
                    }

                } catch (\Exception $e) {
                    Log::error("Purchase Notification Failed: " . $e->getMessage());
                }
            }
            // ============================================================

            if ($request->ajax()) {
                // تجهيز بيانات الواتساب للمورد (إذا كانت الخدمة مفعلة)
                $whatsappData = null;
                
                // التأكد من أن قيمة $store موجودة ومحملة
                if (!$isDraft && isset($store) && $store->whatsapp_auto_prompt && $purchase->supplier && $purchase->supplier->phone) {
                    $itemsLines = [];
                    foreach($purchase->items as $item) {
                        $uName = $item->unit->unit_name ?? ($item->product->baseUnit->unit_name ?? 'قطعة');
                        $itemsLines[] = "• " . ($item->product->name_ar ?? 'منتج') . " ({$item->quantity} {$uName})";
                    }
                    
                    $msgBody = "*أمر شراء / فاتورة مشتريات #{$purchase->invoice_number}*\n";
                    $msgBody .= "التاريخ: " . ($purchase->invoice_date ? $purchase->invoice_date->format('Y-m-d') : now()->format('Y-m-d')) . "\n";
                    $msgBody .= "المورد: " . ($purchase->supplier->contact_name ?? $purchase->supplier->company_name) . "\n";
                    $msgBody .= "--------------------------\n";
                    $msgBody .= implode("\n", $itemsLines) . "\n";
                    $msgBody .= "--------------------------\n";
                    $msgBody .= "*الإجمالي:* " . number_format($purchase->grand_total, 2) . " د.أ\n";
                    $due = $purchase->grand_total - $purchase->paid_amount;
                    if($due > 0) $msgBody .= "*المتبقي:* " . number_format($due, 2) . " د.أ\n";
                    $msgBody .= "عن متجر: *" . $store->name . "*";

                    $whatsappData = [
                        'phone' => $purchase->supplier->phone,
                        'message' => $msgBody
                    ];
                }

                return response()->json([
                    'success' => true,
                    'id' => $purchase->id,
                    'message' => $isDraft ? 'تم حفظ المسودة' : 'تم حفظ الفاتورة',
                    'whatsapp_data' => $whatsappData
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
        $purchase = Purchase::with(['items.product.units.media', 'items.unit', 'supplier'])->findOrFail($id);
        
        foreach($purchase->items as $item) {
            if($item->product) {
                $img = asset('images/default-product.png');
                
                if($item->unit && $item->unit->getFirstMediaUrl('unit_images')) {
                    $img = $item->unit->getFirstMediaUrl('unit_images');
                } elseif ($item->product->getFirstMediaUrl('products')) {
                    $img = $item->product->getFirstMediaUrl('products');
                }
                
                $item->product->image_url = $img;
                $item->product->scanned_unit_id = $item->product_unit_id;
            }
        }

        $store = Auth::user()->store;
        $suppliers = Contact::where('store_id', $store->id)->whereIn('type', ['supplier', 'both'])->get();
        $taxRates = explode(',', $store->tax_rates ?? '0,15');

        return view('store_owner.purchases.edit', compact('purchase', 'suppliers', 'taxRates'));
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
                
                // 🟢🔴 عكس رصيد المورد عند الحذف
                $supplier = Contact::find($purchase->supplier_id);
                if ($supplier) {
                    $debt = $purchase->grand_total - $purchase->paid_amount;
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
        $purchase = Purchase::with(['items.product', 'items.unit', 'supplier'])->findOrFail($id);

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
            $storeId = Auth::user()->store->id;
            
            $products = Product::where('store_id', $storeId)
                ->where(function($q) use ($term) {
                    $q->where('name_ar', 'like', "%$term%")
                      ->orWhere('name_en', 'like', "%$term%")
                      ->orWhere('sku', 'like', "%$term%") 
                      ->orWhereHas('units', function($q2) use ($term) {
                          $q2->where('barcode', 'like', "%$term%");
                      });
                })
                // 🛑 التعديل هنا: حذفنا media لأنها تسبب الخطأ
                ->with(['units']) 
                ->take(20)
                ->get();
    
            $products->transform(function ($product) use ($term) {
                // إضافة النص للقائمة
                $product->text = $product->name_ar . ' (' . $product->sku . ')';

                $matchedUnit = $product->units->firstWhere('barcode', $term);
                $product->scanned_unit_id = $matchedUnit ? $matchedUnit->id : null;
                
                // جلب الصور يدوياً من الداتابيس (بديل المكتبة المحذوفة)
                $mainImg = asset('images/default-product.png');
                $prodMedia = DB::table('media')->where('model_type', 'App\Models\Product')->where('model_id', $product->id)->first();
                if ($prodMedia) {
                    $mainImg = asset('storage/' . $prodMedia->id . '/' . $prodMedia->file_name);
                }
                $product->main_image = $mainImg;

                // 🔥 تم إزالة الفلترة لعرض جميع الوحدات (حتى البيع فقط) في قسم "الوحدات المرتبطة" 🔥
                //$filteredUnits = $product->units->filter(function($u) {
                //    return $u->is_purchase;
                //});
                //$product->setRelation('units', $filteredUnits->values());

                foreach($product->units as $unit) {
                    $unitMedia = DB::table('media')->where('model_type', 'App\Models\ProductUnit')->where('model_id', $unit->id)->first();
                    $unit->image_url = $unitMedia ? asset('storage/' . $unitMedia->id . '/' . $unitMedia->file_name) : $mainImg;
                }

                return $product;
            });
                
            return response()->json($products);
    
        } catch (\Exception $e) {
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
}
