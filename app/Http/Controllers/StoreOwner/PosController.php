<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;
use App\Mail\StockAlertMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;  // ضروري لتسجيل الأخطاء
use Illuminate\Support\Facades\Mail; // <--- أضف هذا السطر ضروري جداً

class PosController extends Controller
{
    public function index()
    {
        // تم إزالة Artisan::call('optimize:clear') لأنه يسبب مشاكل في قفل الملفات في ويندوز
        
        // ✅ ضمان وجود حساب "صاحب المتجر" عند فتح الصفحة
        // ✅ ضمان وجود حساب "صاحب المتجر" وتحديث القديم إن وجد (إصلاح شامل لجميع التكرارات)
        if (Auth::check() && Auth::user()->store) {
            $store = Auth::user()->store;
            $storeId = $store->id;
            
            // 1. Search for existing store owner record by flag
            $owner = Contact::where('store_id', $storeId)
                            ->where('is_store_owner', true)
                            ->first();

            if (!$owner) {
                // 2. Fallback: Search by Name + Store
                $owner = Contact::where('store_id', $storeId)
                                ->where('contact_name', 'صاحب المتجر')
                                ->first();
            }

            if ($owner) {
                // Update flag if needed
                if (!$owner->is_store_owner) {
                    $owner->update(['is_store_owner' => true]);
                }
            } else {
                // 3. Create new using Store's verified data
                // Priority: Phone -> Email -> Dummy ID
                $phone = $store->phone_number;
                if (!$phone || Contact::where('phone', $phone)->exists()) {
                    $phone = $store->email && !Contact::where('phone', $store->email)->exists() 
                             ? $store->email 
                             : 'OWNER-' . $storeId;
                }

                Contact::create([
                    'store_id' => $storeId,
                    'is_store_owner' => true,
                    'contact_name' => 'صاحب المتجر',
                    'type' => 'customer',
                    'phone' => $phone
                ]);
            }
        }




        $nextInvoice = 'INV-' . date('ymd-Hi');
        
        $lastWithdrawal = Sale::where('store_id', Auth::user()->store->id)->where('is_withdrawal', true)->max('withdrawal_number');
        $nextWithdrawal = 'SOV-' . str_pad(($lastWithdrawal + 1), 4, '0', STR_PAD_LEFT);

        return view('store_owner.pos.index', compact('nextInvoice', 'nextWithdrawal'));
    }

    // 1. بحث المنتجات
    public function searchProducts(Request $request)
    {
        try {
            $term = $request->term;
            $storeId = Auth::user()->store->id;

            $query = Product::where('store_id', $storeId)
                ->where('is_active', true)
                ->where(function($q) use ($term) {
                    $q->where('name_ar', 'LIKE', "%{$term}%")
                      ->orWhere('sku', 'LIKE', "%{$term}%")
                      ->orWhereHas('units', function($q2) use ($term) {
                          $q2->where('barcode', 'LIKE', "%{$term}%");
                      });
                });

            // للمطاعم: لا تظهر المكونات الخام في البيع (POS)
            $store = Auth::user()->store;
            if ($store->type == 'restaurant') {
                $query->where('product_type', '!=', 'ingredient');
            }

            $products = $query->with(['baseUnit', 'units']) 
                ->take(20)
                ->get();

            $results = $products->map(function($p) use ($term) {
                $productImg = $p->image_url; 
                
                $hasExpired = \App\Models\ProductBatch::where('product_id', $p->id)
                    ->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '<', now())
                    ->exists();
                
                $isNearExpiry = \App\Models\ProductBatch::where('product_id', $p->id)
                    ->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '>=', now())
                    ->whereRaw('expiry_date <= DATE_ADD(NOW(), INTERVAL alert_days DAY)')
                    ->exists();

                $units = collect();
                if($p->baseUnit && $p->baseUnit->is_sale) {
                    $units->push([
                        'unit_id' => $p->baseUnit->id, 
                        'unit_name' => $p->baseUnit->unit_name ?? 'قطعة', 
                        'price' => $p->baseUnit->selling_price, 
                        'barcode' => $p->baseUnit->barcode ?? $p->sku, 
                        'image' => $productImg,
                        'factor' => 1
                    ]);
                }
                
                foreach($p->units as $u) {
                    if($u->is_base_unit || !$u->is_sale) continue;
                    
                    $unitImg = $u->image;

                    $units->push([
                        'unit_id' => $u->id, 
                        'unit_name' => $u->unit_name, 
                        'price' => $u->selling_price, 
                        'barcode' => $u->barcode, 
                        'image' => $unitImg,
                        'factor' => $u->conversion_factor ?? 1 
                    ]);
                }

                $matchedUnit = $units->firstWhere('barcode', $term);
                $defaultUnit = $matchedUnit ?? $units->first();
                $displayQty = (float)($p->current_stock ?? 0);

                return [
                    'id' => $p->id,
                    'name_ar' => $p->name_ar,
                    'image' => $productImg,
                    'base_image' => $productImg, 
                    'quantity' => max(0, $displayQty), 
                    'base_quantity' => $displayQty, // سيظهر الآن بدون أصفار زائدة
                    'alert_status' => $hasExpired ? 'expired' : ($isNearExpiry ? 'near' : 'ok'),
                    'alert_msg' => $hasExpired ? '⚠️ يوجد كميات منتهية!' : ($isNearExpiry ? '⚠️ قارب على الانتهاء' : ''),
                    'default_unit_id' => $defaultUnit['unit_id'] ?? null,
                    'default_price' => (float)($defaultUnit['price'] ?? 0),
                    'default_barcode' => $defaultUnit['barcode'] ?? $p->sku,
                    'available_units' => $units->values()
                ];
            });

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. بحث العملاء
    public function searchCustomers(Request $request)
    {
        $term = $request->term;
        $storeId = Auth::user()->store->id;

        // ✅ التأكد من وجود "صاحب المتجر"
        $ownerContact = Contact::where('store_id', $storeId)->where('is_store_owner', true)->first();
        if(!$ownerContact) {
             // Fallback if not created in index (rare)
             $ownerContact = Contact::create([
                 'store_id' => $storeId, 'is_store_owner' => true, 
                 'contact_name' => 'صاحب المتجر', 'type' => 'customer', 'phone' => '0'
             ]);
        }

        $query = Contact::where('store_id', $storeId)
            ->whereIn('type', ['customer', 'both']);

        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('contact_name', 'LIKE', "%{$term}%")
                  ->orWhere('phone', 'LIKE', "%{$term}%");
            });
        }
            
        $customers = $query->take(50)->get();

        // دمج صاحب المتجر إذا لم يكن موجوداً في النتائج وكان البحث فارغاً أو يطابق
        if (!$customers->contains('id', $ownerContact->id)) {
            if (empty($term) || mb_stripos('صاحب المتجر', $term) !== false) {
                $customers->prepend($ownerContact);
            }
        }

        $results = $customers->map(function($c) {
            // ✅ ضمان التعرف على صاحب المتجر حتى لو لم يكن العلم مضبوطاً في قاعدة البيانات
            $isOwner = $c->is_store_owner || mb_stripos($c->contact_name, 'صاحب المتجر') !== false;

            return [
                'id' => $c->id,
                'text' => $c->contact_name . ($isOwner ? ' 👑' : ' (' . ($c->phone ?? '-') . ')'), 
                'balance' => $c->balance ?? 0,
                'is_store_owner' => $isOwner
            ];
        });

        return response()->json(['results' => $results]);
    }

    // 3. حفظ الفاتورة (نسخة معدلة لتدعم الإيميل والواتساب + المسحوبات)
    public function storeInvoice(Request $request, InventoryService $inventoryService) 
    {
        $user = Auth::user();
        $store = $user->store;
        $storeId = $store->id;
        
        $customerId = $request->input('customer_id');
        $netTotal = (float) $request->input('total', 0);
        $discountAmount = (float) $request->input('discount_amount', 0);
        $roundingDiff = (float) $request->input('rounding_diff', 0);
        $payments = $request->input('payments', []);
        $items = $request->input('items', []);

        DB::beginTransaction();

        try {
            // فحص هل هو صاحب المتجر (مسحوبات)
            $isWithdrawal = false;
            if ($customerId) {
                $contact = Contact::find($customerId);
                if ($contact && $contact->is_store_owner) $isWithdrawal = true;
            }

            // 1. الحسابات
            $totalPaid = 0;
            if (!$isWithdrawal) {
                foreach ($payments as $pay) { $totalPaid += (float)($pay['amount'] ?? 0); }
                $debtAmount = round($netTotal - $totalPaid, 2);

                if ($debtAmount > 0.01 && empty($customerId)) {
                    return response()->json(['error' => 'customer_required', 'message' => '⚠️ لا يمكن تسجيل دين على عميل عام.'], 422);
                }
            } else {
                // في حالة المسحوبات: لا دفع ولا دين (فقط توثيق)
                $totalPaid = 0;
                $debtAmount = 0;
            }

            $itemsCosts = [];
            $stockAlerts = []; 

            // 2. معالجة الأصناف
            foreach ($items as $index => $item) {
                $product = Product::where('id', $item['id'])->lockForUpdate()->first();
                if ($product) {
                    $factor = 1;
                    $selUnitId = $item['selected_unit_id'] ?? null;
                    if ($selUnitId) {
                        $unit = \App\Models\ProductUnit::where('id', $selUnitId)->where('product_id', $product->id)->first();
                        if ($unit) $factor = ($unit->is_base_unit || $unit->id == $product->base_unit_id) ? 1 : $unit->conversion_factor;
                    }
                    
                    $qtyToDeduct = $item['qty'] * $factor;

                    if ($product->track_stock && (float)$product->current_stock < $qtyToDeduct) {
                        return response()->json(['error' => 'stock_error', 'message' => "الكمية غير كافية للمنتج: <b>{$product->name_ar}</b>"], 422);
                    }

                    $itemsCosts[$index] = $inventoryService->reduceStock($product, $qtyToDeduct);
                    
                    // ✅ التحقق من الكسور (Validation for Fractions)
                    $unitName = 'قطعة';
                    if ($selUnitId && isset($unit)) $unitName = $unit->unit_name;
                    elseif ($product->baseUnit) $unitName = $product->baseUnit->unit_name;
                    
                    $isKilo = preg_match('/kilo|kg|كيلو|كغ/i', $unitName);
                    if (!$isKilo && fmod((float)$item['qty'], 1) !== 0.0) {
                         return response()->json(['error' => 'stock_error', 'message' => "خطأ: الوحدة ($unitName) للمنتج ({$product->name_ar}) لا تقبل الكسور!"], 422);
                    }
                    
                    // منطق التنبيه
                    $product->refresh();
                    if ($product->track_stock) {
                        $currentStock = (float)$product->current_stock;
                        if ($currentStock <= 0) {
                            $stockAlerts[] = "🔴 نفذت الكمية: {$product->name_ar}";
                        } elseif ($currentStock <= $product->alert_quantity) {
                            $stockAlerts[] = "⚠️ مخزون منخفض: {$product->name_ar} (باقي: {$currentStock})";
                        }
                    }
                }
            }

            // 3. معالجة رصيد العميل (إذا لم يكن مسحوبات)
            if ($customerId && !$isWithdrawal) {
                $contact = Contact::where('id', $customerId)->where('store_id', $storeId)->lockForUpdate()->first();
                if ($contact) { 
                    if ($request->has('update_limit_to') && is_numeric($request->update_limit_to)) {
                        $contact->credit_limit = $request->update_limit_to;
                        $contact->save(); $contact->refresh();
                    }

                    $currentBalance = (float)($contact->balance ?? 0);
                    $newBalance = $currentBalance;
                    
                    if ($debtAmount > 0) {
                        $newBalance -= $debtAmount;
                        $limit = (float)($contact->credit_limit ?? 0);
                        if ($limit > 0 && $newBalance < 0 && abs($newBalance) > $limit) {
                            return response()->json([
                                'error' => 'credit_limit_exceeded', 
                                'message' => "تجاوز العميل حد الدين المسموح!", 
                                'current_limit' => $limit,
                                'new_debt' => abs($newBalance),
                                'required_limit' => abs($newBalance),
                                'difference' => abs($newBalance) - $limit
                            ], 422);
                        }
                    } elseif ($debtAmount < 0) {
                        $newBalance += abs($debtAmount);
                    }
                    
                    $contact->balance = $newBalance;
                    $contact->save();
                }
            }

            // 4. حفظ الفاتورة
            $sale = new Sale();
            $sale->store_id = $storeId; $sale->contact_id = $customerId;
            $sale->user_id = $user->id;
            $sale->total = $netTotal; $sale->discount = $discountAmount;
            $sale->rounding = $roundingDiff; $sale->paid = $totalPaid;
            $sale->due = max(0, $debtAmount);
            
            if ($isWithdrawal) {
                $sale->is_withdrawal = true;
                $sale->withdrawal_number = Sale::where('store_id', $storeId)->where('is_withdrawal', true)->max('withdrawal_number') + 1;
            }

            $sale->save();

            if (!$isWithdrawal) {
                foreach ($payments as $pay) Payment::create(['sale_id' => $sale->id, 'method' => $pay['method'], 'amount' => $pay['amount']]);
            }

            foreach ($items as $index => $item) {
                SaleItem::create([
                    'sale_id' => $sale->id, 'product_id' => $item['id'],
                    'quantity' => $item['qty'], 'price' => $item['price'],
                    'total' => $item['qty'] * $item['price'], 
                    'unit_id' => $item['selected_unit_id'] ?? null,
                    'cost' => $itemsCosts[$index] ?? 0
                ]);
            }

            DB::commit();

            // ============================================================
            // 🔥 منطقة الإشعارات (إيميل وواتساب) 🔥
            // ============================================================
            try {
                // 1. توليد ملف PDF للفاتورة (محلياً وبسرعة)
                $pdfPath = null;
                $pdfUrl = null;
                $pdfName = 'invoice_' . $sale->id . '.pdf';

                try {
                    $arabicService = new \App\Services\ArabicTextService();
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store_owner.pos.invoice_pdf', compact('sale', 'store', 'arabicService'))
                        ->setPaper('a4', 'portrait')
                        ->setOptions([
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => false, // 🚀 هام جداً للسرعة local
                            'defaultFont' => 'DejaVu Sans'
                        ]);
                    
                    $tempDir = public_path('temp_reports');
                    if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
                    
                    $pdfPath = $tempDir . '/' . $pdfName;
                    $pdf->save($pdfPath);
                    $pdfUrl = asset('temp_reports/' . $pdfName);

                } catch (\Exception $pdfEx) {
                    Log::error("Invoice PDF Generation Error: " . $pdfEx->getMessage());
                }

                $isCredit = ($sale->due > 0);
                $stockAlertLines = [];
                $emailAlertData = [];
                
                foreach ($items as $item) {
                    $prod = Product::with('baseUnit')->find($item['id']);
                    if ($prod && $prod->track_stock) {
                        $currentStock = (float)$prod->current_stock; 
                        $alertLimit = (float)$prod->alert_quantity;
                        
                        if ($currentStock <= $alertLimit) {
                            $barcode = $prod->baseUnit ? $prod->baseUnit->barcode : $prod->sku; 
                            $barcodeStr = $barcode ? $barcode : '---';
                            
                            $header = $currentStock <= 0 ? "🔴 نفذت الكمية" : "⚠️ مخزون منخفض";
                            $msgSuffix = $isWithdrawal ? " (بسبب سحب صاحب المتجر)" : "";
                            
                            $stockAlertLines[] = "{$header}{$msgSuffix}\n📦 {$prod->name_ar}\n🔢 {$barcodeStr}\n📉 الحالية: {$currentStock}";
                            
                            $emailAlertData[] = [
                                'name' => $prod->name_ar,
                                'stock' => $currentStock
                            ];
                        }
                    }
                }
                
                $stockBody = !empty($stockAlertLines) ? implode("\n", $stockAlertLines) : "";

                // 🟢 2. منطق الواتساب (مرفق PDF) 🟢
                if ($store->notify_whatsapp && $store->phone_number) {
                    try {
                        $waMsg = "";
                        
                        // إشعار المبيعات
                        if (!$isWithdrawal && $store->wa_notify_sales) {
                            $sendInv = false;
                            if ($store->wa_sales_credit_only) { 
                                if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true; 
                            } else { 
                                if ($netTotal >= ($store->wa_sales_min ?? 0)) $sendInv = true; 
                                if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true; 
                            }

                            if ($sendInv) {
                                $waMsg .= "🧾 *فاتورة جديدة #{$sale->id}*\n";
                                $waMsg .= "💰 القيمة: {$netTotal}\n";
                                $waMsg .= "👤 العميل: " . ($sale->contact ? $sale->contact->contact_name : 'نقدي') . "\n";
                                if ($isCredit) $waMsg .= "⚠️ متبقي عليه: {$sale->due}\n";
                            }
                        }

                        // إلحاق تنبيهات المخزون
                        if ($store->wa_notify_stock && !empty($stockBody)) {
                            $waMsg .= "\n" . $stockBody . "\n";
                        }
                        
                        if (!empty($waMsg)) {
                            // إرسال الملف (مع النص كـ Caption) إذا نجح توليد الـ PDF
                            if ($pdfUrl) {
                                app(\App\Services\WhatsAppService::class)->sendFile(
                                    $store->phone_number, 
                                    $pdfUrl, 
                                    trim($waMsg), 
                                    $storeId,
                                    $pdfName // اسم الملف عند الاستيلام
                                );
                            } else {
                                // Fallback: إرسال نص فقط إذا فشل الـ PDF
                                app(\App\Services\WhatsAppService::class)->send(
                                    $store->phone_number, 
                                    trim($waMsg), 
                                    $storeId 
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("POS WhatsApp Error: " . $e->getMessage());
                    }
                }

                // 🔵 3. منطق الإيميل (مرفق PDF) 🔵
                if ($store->notify_email && $store->email) {
                    try {
                        // إرسال الفاتورة (مطلوب دائماً للمبيعات)
                        if (!$isWithdrawal && $pdfPath && file_exists($pdfPath)) {
                            $subject = "فاتورة مبيعات جديدة #{$sale->id}";
                            $body = "مرفق طيه فاتورة المبيعات رقم #{$sale->id}.\nالقيمة الإجمالية: {$netTotal}";
                            
                            Mail::to($store->email)->send(new \App\Mail\ReportMail(
                                $subject,
                                $body,
                                $pdfPath,
                                $pdfName
                            ));
                        }

                        // تنبيه المخزون (منفصل)
                        if ($store->email_notify_stock && !empty($emailAlertData)) {
                            $reason = $isWithdrawal ? "سحب كمية من قبل صاحب المتجر" : "عملية بيع جديدة";
                            Mail::to($store->email)->send(new StockAlertMail($emailAlertData, $store->name, $reason));
                        }
                    } catch (\Exception $e) {
                         Log::error("POS Email Error: " . $e->getMessage());
                    }
                }

            } catch (\Exception $e) { 
                Log::error("General Notif Error: " . $e->getMessage()); 
            }

            if ($isWithdrawal) {
                return response()->json(['success' => true, 'message' => 'تم تسجيل المسحوبات', 'invoice_id' => $sale->id]);
            }

            // تجهيز بيانات الواتساب للعرض في النافذة (إذا كانت الخدمة مفعلة)
            $whatsappData = null;
            if ($store->whatsapp_auto_prompt && $sale->contact && $sale->contact->phone) {
                $itemsLines = [];
                foreach($sale->items as $item) {
                    $uName = $item->unit->unit_name ?? ($item->product->baseUnit->unit_name ?? 'قطعة');
                    $itemsLines[] = "• " . ($item->product->name_ar ?? 'منتج') . " ({$item->quantity} {$uName})";
                }
                
                $msgBody = "*فاتورة مبيعات #{$sale->id}*\n";
                $msgBody .= "التاريخ: " . $sale->created_at->format('Y-m-d h:i A') . "\n";
                $msgBody .= "العميل: " . $sale->contact->contact_name . "\n";
                $msgBody .= "--------------------------\n";
                $msgBody .= implode("\n", $itemsLines) . "\n";
                $msgBody .= "--------------------------\n";
                $msgBody .= "*الإجمالي:* " . number_format($sale->total, 2) . " د.أ\n";
                if($sale->due > 0) $msgBody .= "*المتبقي:* " . number_format($sale->due, 2) . " د.أ\n";
                $msgBody .= "شكرًا لتعاملكم معنا 🙏\n";
                $msgBody .= "*" . $store->name . "*";

                $whatsappData = [
                    'phone' => $sale->contact->phone,
                    'message' => $msgBody
                ];
            }

            return response()->json([
                'success' => true, 
                'message' => 'تم الحفظ بنجاح', 
                'invoice_id' => $sale->id,
                'whatsapp_id' => $sale->id, // Fallback for some JS
                'whatsapp_data' => $whatsappData,
                'customer_email' => ($sale->contact ? $sale->contact->email : null)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'server_error', 'message' => $e->getMessage()], 500);
        }
    }
    // تعديل المخزون الذكي
    public function quickAdjustStock(Request $request)
    {
        // ... (unchanged)
        $request->validate([ 'product_id' => 'required|exists:products,id', 'new_qty' => 'required|numeric|min:0' ]);
        $product = Product::where('store_id', Auth::user()->store->id)->where('id', $request->product_id)->firstOrFail();
        $oldQty = $product->current_stock;
        $product->current_stock = $request->new_qty; $product->quantity = $request->new_qty; $product->save();
        return response()->json(['success' => true, 'message' => 'تم تعديل المخزون بنجاح']);
    }

    // تحديث تاريخ الصلاحية
    public function updateProductExpiry(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'new_date' => 'required|date|after:today',
            'reason' => 'required|string|min:5'
        ]);

        $user = Auth::user();
        $store = $user->store;

        $batch = \App\Models\ProductBatch::where('product_id', $request->product_id)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->first();

        if (!$batch) {
            return response()->json(['error' => 'لا توجد دفعات لهذا المنتج لتحديثها'], 404);
        }

        $oldDate = $batch->expiry_date;
        $batch->expiry_date = $request->new_date;
        $batch->save();

        $message = "قام الموظف ({$user->name}) بتمديد صلاحية المنتج ({$batch->product->name_ar}) من ($oldDate) إلى ({$request->new_date}). السبب: {$request->reason}";

        try {
            DB::table('notifications')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\ExpiryUpdate',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $store->owner_id,
                'data' => json_encode([
                    'title' => '⚠️ تعديل تاريخ صلاحية يدوي',
                    'message' => $message,
                    'time' => now()
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {}

        return response()->json(['success' => true, 'message' => 'تم تحديث التاريخ وتوثيق العملية، يمكنك البيع الآن']);
    }

    // 4. جلب سجل المبيعات (تعديل لاستثناء المسحوبات)
    public function getRecentSales(Request $request)
    {
        // ... (unchanged logic) ...
        try {
            $storeId = Auth::user()->store->id;
            // ✅ استثناء المسحوبات بشكل افتراضي
            $query = Sale::where('store_id', $storeId)->where('is_withdrawal', false)->with(['contact', 'user', 'returns']);

            if ($request->filled('invoice_no')) {
                $query->where(function($q) use ($request) {
                    $q->where('id', 'LIKE', "%{$request->invoice_no}%")
                      ->orWhereRaw("CONCAT('INV-', id) LIKE ?", ["%{$request->invoice_no}%"]);
                });
            }

            if ($request->filled('customer_id') && $request->customer_id != 'all') {
                if($request->customer_id == 'cash') $query->whereNull('contact_id');
                else $query->where('contact_id', $request->customer_id);
            }
            if ($request->filled('from_date')) $query->whereDate('created_at', '>=', $request->from_date);
            if ($request->filled('to_date')) $query->whereDate('created_at', '<=', $request->to_date);

            if ($request->filled('payment_status')) {
                $status = $request->payment_status;
                if ($status == 'paid') {
                    // المدفوع: المتبقي صفر (أو سالب بسيط) + المدفوع لا يتجاوز الإجمالي بفرق واضح (0.01)
                    $query->where('due', '<=', 0.01)->whereRaw('paid <= total + 0.01');
                }
                elseif ($status == 'unpaid') {
                    // غير مدفوع: المدفوع صفر تقريباً
                    $query->where('paid', '<', 0.01);
                }
                elseif ($status == 'partial') {
                    // جزئي: دفع جزء (أكثر من 0.01) وبقي جزء (أكثر من 0.01)
                    $query->where('paid', '>=', 0.01)->where('due', '>', 0.01);
                }
                elseif ($status == 'overpaid') {
                    // دفعة زائدة: المدفوع أكبر من الإجمالي بفرق وضع (0.01)
                    $query->whereRaw('paid > total + 0.01');
                }
                // ✅ فلتر المرتجعات
                elseif ($status == 'has_returns') {
                    $query->where('total_returns', '>', 0);
                }
            }

            $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'));
            
            $perPage = $request->input('per_page', 10);
            $sales = ($perPage == 'all') ? $query->paginate(200) : $query->paginate((int)$perPage);

            $sales->getCollection()->transform(function($s) {
                $st = 'unpaid'; 
                if ($s->paid > $s->total + 0.01) $st = 'overpaid';
                elseif ($s->due <= 0.01) $st = 'paid';
                elseif ($s->paid > 0) $st = 'partial';

                return [
                    'id' => $s->id,
                    'invoice_number' => 'INV-' . $s->id,
                    'customer_name' => optional($s->contact)->contact_name ?? 'عميل نقدي',
                    'user_name' => optional($s->user)->name ?? 'غير محدد',
                    'total' => (float)$s->total,
                    'paid' => (float)$s->paid,
                    'due' => (float)$s->due,
                    'status' => $st,
                    'date' => $s->created_at->format('Y-m-d h:i A'),
                    // ✅ بيانات المرتجعات
                    'total_returns' => (float)($s->total_returns ?? 0),
                    'has_returns' => $s->returns->count() > 0 || ($s->total_returns ?? 0) > 0,
                ];
            });

            $store = Auth::user()->store;
            
            return response()->json([
                'sales' => $sales,
                'store_info' => [
                    'name' => $store->name,
                    'logo' => $store->logo_url
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // تقرير المسحوبات المخصص
    public function getWithdrawalsReport(Request $request)
    {
        $storeId = Auth::user()->store->id;
        
        $query = Sale::where('store_id', $storeId)
                     ->where('is_withdrawal', true)
                     ->where('is_withdrawal', true)
                     ->with(['items.product.baseUnit', 'items.product.units']); // تحديث: جلب الوحدات لحساب التكلفة

        if ($request->filled('from_date')) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->filled('to_date')) $query->whereDate('created_at', '<=', $request->to_date);

        $withdrawals = $query->latest()->paginate(20);

        // حساب الإجماليات
        $totalCost = 0;
        $totalSale = 0;

        // تحويل البيانات للحساب والعرض
        $withdrawals->getCollection()->transform(function($sale) use (&$totalCost, &$totalSale) {
            $cost = 0;
            $itemsCount = 0;
            
            foreach($sale->items as $item) {
                // ✅ تحديث (FIFO): قراءة التكلفة المخزنة أولاً
                $itemCost = 0;
                
                if (!is_null($item->cost) && $item->cost > 0) {
                     $itemCost = $item->cost;
                } 
                else {
                    // Fallback (طريقة الحساب القديمة)
                    $prod = $item->product;
                    
                    if ($prod) {
                        // محاولة العثور على الوحدة المستخدمة
                        $u = null;
                        if($item->unit_id) {
                             $u = $prod->units->where('id', $item->unit_id)->first();
                        } else {
                             $u = $prod->baseUnit;
                        }

                        if ($u && !empty($u->cost_price) && $u->cost_price > 0) {
                            $itemCost = (float)$u->cost_price * $item->quantity;
                        } else {
                            $baseCost = (float)$prod->last_cost_price;
                            $factor = ($u) ? (float)$u->conversion_factor : 1;
                            $itemCost = ($baseCost * $factor) * $item->quantity;
                        }
                    } else {
                        // fallback للمنتجات المحذوفة (إذا لم يكن هناك cost مخزن أصلاً)
                        $itemCost = 0; 
                    }
                }

                $cost += $itemCost;
                $itemsCount++;
            }

            $totalCost += $cost;
            $totalSale += $sale->total;

            return [
                'id' => $sale->id,
                'number' => 'SOV-' . str_pad($sale->withdrawal_number, 4, '0', STR_PAD_LEFT),
                'date' => $sale->created_at->format('Y-m-d h:i A'),
                'items_count' => $itemsCount,
                'total_sale' => $sale->total,
                'total_cost' => $cost,
            ];
        });

        // إذا كان طلب AJAX (للفلترة)
        if ($request->ajax()) {
            return response()->json([
                'html' => view('store_owner.pos.partials.withdrawals_table', compact('withdrawals'))->render(),
                'totals' => ['cost' => number_format($totalCost, 2), 'sale' => number_format($totalSale, 2)]
            ]);
        }

        return view('store_owner.pos.withdrawals', compact('withdrawals', 'totalCost', 'totalSale'));
    }

    // 5. تفاصيل الفاتورة
    public function getSaleDetails($id)
    {
        try {
            $storeId = Auth::user()->store->id;
            $sale = Sale::where('store_id', $storeId)->where('id', $id)->with(['contact', 'items.product'])->first();
            if (!$sale) return response()->json(['error' => 'غير موجودة'], 404);

            $store = Auth::user()->store;
            
            $storeData = [
                'name' => $store->name, 'address' => $store->address, 'tax_number' => $store->tax_number,
                'logo_url'      => $store->logo_url,
                'stamp_url'     => $store->stamp_url,
                'signature_url' => $store->signature_url,
            ];

            $items = $sale->items->map(function($item) {
                $uName = 'قطعة';
                if($item->unit_id && $u = \App\Models\ProductUnit::find($item->unit_id)) $uName = $u->unit_name;
                elseif($item->product && $item->product->baseUnit) $uName = $item->product->baseUnit->unit_name;

                $currentUnitCost = 0;
                $prod = $item->product;
                
                if ($prod) {
                    $u = $item->unit_id ? \App\Models\ProductUnit::find($item->unit_id) : ($prod->baseUnit ?? null);
                    if ($u && !empty($u->cost_price) && $u->cost_price > 0) {
                        $currentUnitCost = (float)$u->cost_price;
                    } 
                    else {
                        $baseCost = (float)$prod->last_cost_price;
                        $factor = ($u) ? (float)$u->conversion_factor : 1;
                        $currentUnitCost = $baseCost * $factor;
                    }
                }

                return [
                    'name' => optional($prod)->name_ar ?? 'محذوف',
                    'barcode' => $prod ? ($prod->sku ?? (optional($prod->baseUnit)->barcode ?? '---')) : '---',
                    'unit' => $uName,
                    'qty' => (float)$item->quantity,
                    'cost' => $currentUnitCost * (float)$item->quantity, 
                    'price' => (float)$item->price,
                    'total' => (float)$item->total
                ];
            });

            // ✅ تبسيط البيانات المرسلة لمنع الانهيار (Serialization Crash) في XAMPP
            $saleData = [
                'id' => $sale->id,
                'total' => (float)$sale->total,
                'created_at' => $sale->created_at->toDateTimeString(),
                'contact' => $sale->contact ? [ 'contact_name' => $sale->contact->contact_name ] : null,
            ];

            return response()->json(['sale' => $saleData, 'items' => $items, 'store' => $storeData]);
        } catch (\Exception $e) { 
            \Illuminate\Support\Facades\Log::error("GetSaleDetails Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500); 
        }
    }

    public function showSalePartial($id)
    {
        $storeId = Auth::user()->store->id;
        $sale = Sale::where('store_id', $storeId)->where('id', $id)->with(['contact', 'items.product', 'items.unit'])->firstOrFail();
        
        return view('store_owner.pos.partials.show_modal', compact('sale'))->render();
    }

    // 6. حذف الفاتورة
    public function deleteSale($id, InventoryService $inventoryService)
    {
        DB::beginTransaction();
        try {
            $storeId = Auth::user()->store->id;
            $sale = Sale::where('store_id', $storeId)->where('id', $id)->with('items.product')->first();

            if (!$sale) return response()->json(['message' => 'الفاتورة غير موجودة'], 404);

            foreach ($sale->items as $item) {
                if (!$item->product) continue;

                $factor = 1;
                if ($item->unit_id) {
                    $unit = \App\Models\ProductUnit::find($item->unit_id);
                    if ($unit) {
                        // تطابقاً مع التخزين في storeInvoice: إذا كان الوحدة أساسية فالمجهود 1
                        $factor = ($unit->is_base_unit || $unit->id == $item->product->base_unit_id) ? 1 : $unit->conversion_factor;
                    }
                }

                $qtyToReturn = $item->quantity * $factor;
                
                // استخدام الخدمة المخصصة لإرجاع المخزون (لضمان معالجة الدفعات FIFO)
                $inventoryService->incrementStock($item->product, $qtyToReturn);
            }
            
            if ($sale->due > 0 && $sale->contact_id) {
                $contact = Contact::find($sale->contact_id);
                if($contact) {
                    $contact->increment('balance', $sale->due); 
                }
            }

            $sale->items()->delete();
            $sale->delete();

            DB::commit();
            return response()->json(['message' => 'تم حذف الفاتورة وإرجاع المخزون الصحيح بنجاح']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()], 500);
        }
    }

    // 7. دوال الإرجاع
    public function searchReturnInvoices(Request $request)
    {
        $term = trim($request->term);
        $customerId = $request->customer_id;
        $storeId = Auth::user()->store->id;

        if (empty($term)) return response()->json(['invoices' => []]);

        // 1. نبحث عن المنتجات التي تطابق الاسم أو الباركود
        $productIds = Product::where('store_id', $storeId)
            ->where(function($q) use ($term) {
                $q->where('name_ar', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%")
                  ->orWhereHas('units', function($u) use ($term) {
                      $u->where('barcode', 'LIKE', "%{$term}%");
                  });
            })->pluck('id');

        // 2. نبحث عن الفاتورة إذا كان البحث برقم الفاتورة (مثلا INV-123 أو 123)
        $invoiceId = null;
        if (is_numeric($term)) {
            $invoiceId = $term;
        } elseif (preg_match('/INV-(\d+)/i', $term, $matches)) {
            $invoiceId = $matches[1];
        }

        // 3. بناء الاستعلام للأصناف
        $query = SaleItem::whereHas('sale', function($q) use ($storeId, $customerId) {
                $q->where('store_id', $storeId);
                if ($customerId && $customerId !== 'all') $q->where('contact_id', $customerId);
            });

        $query->where(function($q) use ($productIds, $invoiceId) {
            if (!$productIds->isEmpty()) {
                $q->whereIn('product_id', $productIds);
            }
            if ($invoiceId) {
                $q->orWhere('sale_id', $invoiceId);
            }
        });

        $items = $query->with(['sale', 'product', 'unit'])
            ->latest()
            ->take(40)
            ->get()
            ->map(function($item) {
                return [
                    'sale_id' => $item->sale->id,
                    'invoice_no' => 'INV-' . $item->sale->id,
                    'date' => $item->sale->created_at->format('Y-m-d H:i'),
                    'product_name' => $item->product->name_ar,
                    'unit_name' => $item->unit->unit_name ?? ($item->product->baseUnit->unit_name ?? 'قطعة'),
                    'qty' => (float)$item->quantity,
                    'price' => (float)$item->price,
                    'item_id' => $item->id,
                    'customer_name' => optional($item->sale->contact)->contact_name ?? 'عميل نقدي'
                ];
            });

        return response()->json(['invoices' => $items]);
    }

    public function processReturn(Request $request)
    {
        $itemId = $request->item_id;
        $qtyToReturn = (float) $request->return_qty;
        $reason = $request->reason ?? null;
        
        DB::beginTransaction();
        try {
            $saleItem = SaleItem::with(['sale', 'product', 'unit'])->findOrFail($itemId);
            
            if ($qtyToReturn > $saleItem->quantity) {
                return response()->json(['error' => 'الكمية المراد إرجاعها أكبر من المباعة'], 422);
            }

            $factor = ($saleItem->unit && !$saleItem->unit->is_base_unit) ? $saleItem->unit->conversion_factor : 1;
            $stockToAdd = $qtyToReturn * $factor;
            
            if (\Schema::hasColumn('products', 'current_stock')) {
                $saleItem->product->increment('current_stock', $stockToAdd);
            } else {
                $saleItem->product->increment('quantity', $stockToAdd);
            }

            $refundAmount = $qtyToReturn * $saleItem->price;

            // ✅ تسجيل المرتجع في جدول sale_returns
            \App\Models\SaleReturn::create([
                'sale_id' => $saleItem->sale_id,
                'sale_item_id' => $saleItem->id,
                'product_id' => $saleItem->product_id,
                'unit_id' => $saleItem->unit_id,
                'quantity' => $qtyToReturn,
                'price' => $saleItem->price,
                'total' => $refundAmount,
                'user_id' => Auth::id(),
                'reason' => $reason,
            ]);

            $saleItem->decrement('quantity', $qtyToReturn);
            $saleItem->decrement('total', $refundAmount);
            if ($saleItem->quantity <= 0) $saleItem->delete();

            $sale = $saleItem->sale;
            $sale->decrement('total', $refundAmount);
            
            // ✅ تحديث إجمالي المرتجعات
            $sale->increment('total_returns', $refundAmount);
            
            if ($sale->due > 0) {
                $deduct = min($sale->due, $refundAmount);
                $sale->decrement('due', $deduct);
                $refundAmount -= $deduct; 
                
                if ($sale->contact_id) {
                    Contact::find($sale->contact_id)->increment('balance', $deduct);
                }
            }
            
            if ($refundAmount > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => 'cash', 
                    'amount' => -$refundAmount 
                ]);
            }

            $sale->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم الإرجاع بنجاح']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * الحصول على تفاصيل المرتجعات لفاتورة معينة
     */
    public function getSaleReturns($saleId)
    {
        try {
            $storeId = Auth::user()->store->id;
            
            $sale = Sale::where('id', $saleId)
                ->where('store_id', $storeId)
                ->with(['contact', 'items.product', 'items.unit', 'returns.product', 'returns.unit', 'returns.user'])
                ->firstOrFail();
            
            // الأصناف الحالية (بعد الإرجاع)
            $currentItems = $sale->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => optional($item->product)->name_ar ?? '---',
                    'unit_name' => optional($item->unit)->unit_name ?? 'قطعة',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            });
            
            // بناء الأصناف الأصلية (قبل الإرجاع) = الأصناف الحالية + المرتجعات
            $originalItems = collect();
            
            // أولاً: إضافة الأصناف الحالية
            foreach ($currentItems as $item) {
                $originalItems->push([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit_name' => $item['unit_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);
            }
            
            // ثانياً: إضافة الكميات المرتجعة للأصناف الأصلية
            foreach ($sale->returns as $ret) {
                $found = false;
                foreach ($originalItems as $key => $item) {
                    // استخدام abs للمقارنة الآمنة للأرقام العشرية
                    if ($item['product_id'] == $ret->product_id && abs($item['price'] - $ret->price) < 0.01) {
                        // إضافة الكمية المرتجعة للكمية الحالية للحصول على الكمية الأصلية
                        $modifiedItem = $originalItems[$key];
                        $modifiedItem['quantity'] += $ret->quantity;
                        $modifiedItem['total'] += $ret->total;
                        $originalItems[$key] = $modifiedItem;
                        $found = true;
                        break;
                    }
                }
                // إذا لم يوجد الصنف في القائمة الحالية (تم إرجاعه كاملاً)
                if (!$found) {
                    $originalItems->push([
                        'product_id' => $ret->product_id,
                        'product_name' => optional($ret->product)->name_ar ?? '---',
                        'unit_name' => optional($ret->unit)->unit_name ?? 'قطعة',
                        'quantity' => $ret->quantity,
                        'price' => $ret->price,
                        'total' => $ret->total,
                    ]);
                }
            }
            
            return response()->json([
                'sale' => [
                    'id' => $sale->id,
                    'total' => $sale->total,
                    'total_returns' => $sale->total_returns ?? 0,
                    'original_total' => $sale->total + ($sale->total_returns ?? 0),
                    'due' => $sale->due,
                    'created_at' => $sale->created_at->format('Y-m-d H:i'),
                    'customer' => optional($sale->contact)->contact_name ?? 'عميل نقدي',
                ],
                // الأصناف الأصلية (قبل الإرجاع) - للتبويب الأول
                'original_items' => $originalItems->values(),
                // الأصناف الحالية (بعد الإرجاع) - للتبويب الثالث
                'current_items' => $currentItems,
                // سجل المرتجعات
                'returns' => $sale->returns->map(function($ret) {
                    return [
                        'id' => $ret->id,
                        'product_name' => optional($ret->product)->name_ar ?? '---',
                        'unit_name' => optional($ret->unit)->unit_name ?? 'قطعة',
                        'quantity' => $ret->quantity,
                        'price' => $ret->price,
                        'total' => $ret->total,
                        'reason' => $ret->reason,
                        'user' => optional($ret->user)->name ?? '---',
                        'created_at' => $ret->created_at->format('Y-m-d H:i'),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            \Log::error("Sale Returns Error: " . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ في النظام: ' . $e->getMessage()], 500);
        }
    }


    /**
     * توليد تقرير المبيعات كـ PDF
     */
    public function salesReportPdf(Request $request)
    {
        $store = Auth::user()->store;
        $storeId = $store->id;
        
        $query = Sale::where('store_id', $storeId)
            ->with(['contact', 'items.product', 'items.unit', 'user']);
        
        // تطبيق الفلاتر
        if ($request->filled('customer_id')) {
            $query->where('contact_id', $request->customer_id);
        }
        
        if ($request->filled('payment_status')) {
            switch ($request->payment_status) {
                case 'paid': $query->where('due', 0); break;
                case 'unpaid': $query->whereColumn('due', '>=', 'total'); break;
                case 'partial': $query->where('due', '>', 0)->whereColumn('due', '<', 'total'); break;
                case 'has_returns': $query->where('total_returns', '>', 0); break;
            }
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // الترتيب
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);
        
        // الحد
        $limit = $request->get('limit', 'all');
        if ($limit !== 'all' && is_numeric($limit)) {
            $query->take((int) $limit);
        }
        
        // زيادة الزمن والذاكرة للتقارير الكبيرة
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $sales = $query->get();
        
        // حساب الإجماليات
        $totals = [
            'count' => $sales->count(),
            'sum_total' => $sales->sum('total'),
            'sum_paid' => $sales->sum(fn($s) => $s->total - $s->due),
            'sum_due' => $sales->sum('due'),
        ];
        
        // استخدام خدمة معالجة النص العربي
        $arabicService = new \App\Services\ArabicTextService();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store_owner.sales.pdf_report', compact('sales', 'store', 'totals', 'arabicService'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => false,
                      'isFontSubsettingEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);
        
        if ($request->get('output') == 'url') {
            $filename = 'sales_report_' . date('Ymd_His') . '_' . uniqid() . '.pdf';
            $path = public_path('temp_reports');
            if (!file_exists($path)) mkdir($path, 0777, true);
            $pdf->save($path . '/' . $filename);
            return response()->json([
                'url' => asset('temp_reports/' . $filename),
                'filename' => $filename
            ]);
        }

        return $pdf->download('sales_report.pdf');
    }

    /**
     * توليد ملف PDF للمرتجعات وإرجاع الرابط للمشاركة
     */
    public function generateReturnsPdf($saleId)
    {
        try {
            $storeId = Auth::user()->store->id;
            $sale = Sale::where('id', $saleId)
                ->where('store_id', $storeId)
                ->with(['contact', 'items.product', 'items.unit', 'returns.product', 'returns.unit', 'returns.user'])
                ->firstOrFail();

            $store = Auth::user()->store;
            
            // استخدام خدمة معالجة النص العربي
            $arabicService = new \App\Services\ArabicTextService();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store_owner.pos.returns_pdf', compact('sale', 'store', 'arabicService'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);

            $filename = 'returns_report_' . $sale->id . '_' . date('Ymd_His') . '.pdf';
            $path = public_path('temp_reports');
            
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            // تنظيف الملفات القديمة
            foreach (glob($path . '/*.pdf') as $file) {
                if (filemtime($file) < time() - 3600) { // حذف ما هو أقدم من ساعة
                    @unlink($file); 
                }
            }

            $pdf->save($path . '/' . $filename);
            
            return response()->json([
                'success' => true,
                'url' => asset('temp_reports/' . $filename),
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            \Log::error("Returns PDF Error: " . $e->getMessage());
            return response()->json(['error' => 'فشل توليد ملف PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * توليد ملف PDF للفاتورة للمشاركة
     */
    public function invoicePdf($id)
    {
        try {
            $storeId = Auth::user()->store->id;
            $sale = Sale::where('id', $id)
                ->where('store_id', $storeId)
                ->with(['contact', 'items.product', 'items.unit', 'user'])
                ->firstOrFail();

            $store = Auth::user()->store;
            
            // استخدام خدمة معالجة النص العربي
            $arabicService = new \App\Services\ArabicTextService();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store_owner.pos.invoice_pdf', compact('sale', 'store', 'arabicService'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);

            $filename = 'invoice_' . $sale->id . '_' . date('Ymd_His') . '.pdf';
            $path = public_path('temp_reports');
            
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            // تنظيف الملفات القديمة
            foreach (glob($path . '/*.pdf') as $file) {
                if (filemtime($file) < time() - 3600) { 
                    @unlink($file); 
                }
            }

            $pdf->save($path . '/' . $filename);
            
            return response()->json([
                'success' => true,
                'url' => asset('temp_reports/' . $filename),
                'filename' => $filename,
                'customer_phone' => $sale->contact ? $sale->contact->phone : null
            ]);

        } catch (\Exception $e) {
            \Log::error("Invoice PDF Error: " . $e->getMessage());
            return response()->json(['error' => 'فشل توليد ملف PDF: ' . $e->getMessage()], 500);
        }
    }
}