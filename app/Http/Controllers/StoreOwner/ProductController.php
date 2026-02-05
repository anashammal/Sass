<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function serveMedia($path)
    {
        $originalPath = $path;
        // فك التشفير لضمان قراءة الأسماء العربية والرموز
        $path = urldecode($path);
        $path = explode('?', $path)[0];
        
        $fullPath = storage_path('app/public/' . $path);
        
        // محاولة البحث في مسارات بديلة (قديمة أو مباشرة)
        if (!file_exists($fullPath)) {
            $altPath = str_replace(['public/', 'storage/', 'stores/'], '', $path);
            $tryPaths = [
                storage_path('app/public/' . $altPath),
                public_path($path),
                public_path($altPath),
                storage_path('app/' . $path),
            ];
            
            foreach ($tryPaths as $tp) {
                if (file_exists($tp)) {
                    $fullPath = $tp;
                    break;
                }
            }
        }

        // تسجيل خطأ في حال فقدان الملف تماماً للمساعدة في التتبع
        if (!file_exists($fullPath)) {
            Log::warning("serveMedia: File NOT FOUND", [
                'provided_path' => $originalPath,
                'decoded_path' => $path,
                'target_full_path' => $fullPath
            ]);
            abort(404);
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'mp4'  => 'video/mp4',
            'mp3'  => 'audio/mpeg',
            'webp' => 'image/webp',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
        ];

        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // للمستندات غير الصور والـ PDF، نفضل التحميل بدلاً من العرض
        $disposition = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mp3', 'svg']) 
            ? 'inline' 
            : 'attachment';

        return response()->file($fullPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => $disposition . '; filename="' . basename($fullPath) . '"',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function index(Request $request)
    {
        $storeId = Auth::user()->store->id;
        $query = Product::where('store_id', $storeId)->with(['baseUnit', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('units', function($q2) use ($search) {
                      $q2->where('barcode', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
             $query->whereIn('category_id', (array)$request->category_id);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status);
        }

        $products = $query->latest()->paginate($request->input('per_page', 10))->withQueryString();

        $prodStats = [
            'total' => \App\Models\Product::where('store_id', $storeId)->count(),
            'low_stock' => \App\Models\Product::where('store_id', $storeId)
                            ->whereColumn('current_stock', '<=', 'alert_quantity') 
                            ->where('current_stock', '>', 0)
                            ->count(),
            'out_of_stock' => \App\Models\Product::where('store_id', $storeId)
                            ->where('current_stock', '<=', 0)
                            ->count(),
        ];

        $categories = \App\Models\Category::where('store_id', $storeId)->get();
        $store = Auth::user()->store;

        if ($request->ajax()) {
            return view('store_owner.products.partials.table_rows', compact('products', 'store'))->render();
        }

        return view('store_owner.products.index', compact('products', 'prodStats', 'categories', 'store'));
    }

    public function create() 
    { 
        $store = Auth::user()->store; 
        $categories = Category::where('store_id', $store->id)->get(); 
        $taxRates = explode(',', $store->tax_rates ?? '0,15');
        
        $ingredients = [];
        if ($store->type == 'restaurant') {
            $ingredients = Product::where('store_id', $store->id)
                ->whereIn('product_type', ['standard', 'ingredient'])
                ->with('units')
                ->get();
        }

        return view('store_owner.products.create', compact('categories', 'store', 'taxRates', 'ingredients')); 
    }

    public function store(Request $request) 
    {
        if (empty($request->base_unit_name) && $request->filled('base_unit_select')) {
            $request->merge(['base_unit_name' => $request->base_unit_select]);
        }

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'category_id' => 'required',
            'base_unit_name' => 'required',
            'base_barcode' => 'nullable|unique:product_units,barcode',
        ]);

        try {
            DB::beginTransaction();
            
            $barcode = $request->base_barcode;
            if (empty($barcode)) {
                do {
                    $barcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
                } while (ProductUnit::where('barcode', $barcode)->exists());
            }

            $product = Product::create([
                'store_id' => Auth::user()->store->id,
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'sku' => $barcode,
                'alert_quantity' => $request->alert_quantity ?? 5,
                'expiry_warning_days' => $request->expiry_warning_days ?? 30,
                'tax_percent' => $request->tax_percent ?? 0,
                'is_active' => $request->has('is_active'),
                'product_type' => $request->product_type ?? 'standard',
            ]);

            if ($request->hasFile('base_unit_image')) {
                $product->addMediaFromRequest('base_unit_image')->toMediaCollection('products');
            }

            $factor = (float)($request->pieces_per_unit ?? 1);
            $purchasePrice = (float)$request->purchase_price;
            $baseCost = ($factor > 0) ? ($purchasePrice / $factor) : 0;

            $product->units()->create([
                'unit_name' => $request->base_unit_name, 
                'conversion_factor' => $factor,
                'purchase_price' => $purchasePrice,
                'cost_price' => $baseCost,
                'selling_price' => (float)$request->base_selling_price,
                'profit_percent' => (float)$request->base_profit_percent,
                'barcode' => $barcode,
                'is_base_unit' => true,
                'is_purchase' => $request->has('base_is_purchase'), 
                'is_sale' => $request->has('base_is_sale'),
            ]);

            if ($request->has('units') && is_array($request->units)) {
                foreach ($request->units as $index => $unitData) {
                    $uName = (!empty($unitData['name'])) ? $unitData['name'] : ($unitData['name_select'] ?? 'وحدة');
                    
                    $uBarcode = $unitData['barcode'] ?? null;
                    if (empty($uBarcode)) {
                        do {
                            $uBarcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
                        } while (ProductUnit::where('barcode', $uBarcode)->exists());
                    }

                    $uFactor = (float)($unitData['factor'] ?? 1);
                    $uCost = $baseCost * $uFactor;

                    $extraUnit = $product->units()->create([
                        'unit_name' => $uName,
                        'conversion_factor' => $uFactor,
                        'barcode' => $uBarcode,
                        'cost_price' => $uCost,
                        'selling_price' => (float)($unitData['selling_price'] ?? 0),
                        'profit_percent' => (float)($unitData['profit_percent'] ?? 0),
                        'is_base_unit' => false,
                        'is_purchase' => isset($unitData['is_purchase']),
                        'is_sale' => isset($unitData['is_sale']),
                    ]);
                    
                    if ($request->hasFile("units.$index.image")) {
                        $extraUnit->addMediaFromRequest("units.$index.image")->toMediaCollection('unit_images');
                    }
                }
            }

            // --- حفظ المكونات (Recipe) إذا كان النوع وجبة ---
            if ($product->product_type == 'meal' && $request->has('recipe')) {
                foreach ($request->recipe as $recipeData) {
                    if (!empty($recipeData['ingredient_id'])) {
                        $ing = Product::find($recipeData['ingredient_id']);
                        $product->recipes()->create([
                            'ingredient_product_id' => $recipeData['ingredient_id'],
                            'quantity' => $recipeData['quantity'] ?? 1,
                            'unit_id' => $ing->baseUnit ? $ing->baseUnit->id : null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('store.products.index')->with('success', 'تم حفظ المنتج والوحدات بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        if ($product->store_id !== Auth::user()->store->id) abort(403);
        $store = Auth::user()->store;
        $categories = Category::where('store_id', $store->id)->get();
        $taxRates = explode(',', $store->tax_rates ?? '0,15');
        
        $ingredients = [];
        if ($store->type == 'restaurant') {
            $ingredients = Product::where('store_id', $store->id)
                ->whereIn('product_type', ['standard', 'ingredient'])
                ->with('units')
                ->get();
            $product->load('recipes.ingredient');
        }

        return view('store_owner.products.edit', compact('product', 'categories', 'store', 'taxRates', 'ingredients'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->store_id !== Auth::user()->store->id) abort(403);

        if ($request->filled('base_unit_select') && $request->base_unit_select !== 'custom') {
            $request->merge(['base_unit_name' => $request->base_unit_select]);
        }

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'category_id' => 'required',
            'base_unit_name' => 'required',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        if ($request->filled('base_barcode')) {
            $exists = ProductUnit::where('barcode', $request->base_barcode)
                ->where('product_id', '!=', $product->id)
                ->exists();

            if ($exists) {
                return back()->withInput()->withErrors(['base_barcode' => 'هذا الباركود مستخدم بالفعل لمنتج آخر!']);
            }
        }

        try {
            DB::beginTransaction();

            $product->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'sku' => $request->base_barcode, 
                'alert_quantity' => $request->alert_quantity,
                'expiry_warning_days' => $request->expiry_warning_days,
                'tax_percent' => $request->tax_percent ?? 0,
                'is_active' => $request->has('is_active'),
                'product_type' => $request->product_type ?? 'standard',
            ]);

            if ($request->hasFile('base_unit_image')) {
                $product->clearMediaCollection('products');
                $product->addMediaFromRequest('base_unit_image')->toMediaCollection('products');
            }

            $factor = (float)($request->pieces_per_unit ?? 1);
            $purchasePrice = (float)$request->purchase_price;
            $baseCost = ($factor > 0) ? ($purchasePrice / $factor) : 0;

            $product->baseUnit()->update([
                'unit_name' => $request->base_unit_name,
                'conversion_factor' => $factor,
                'purchase_price' => $purchasePrice,
                'cost_price' => $baseCost,
                'selling_price' => (float)$request->base_selling_price,
                'profit_percent' => (float)$request->base_profit_percent,
                'barcode' => $request->base_barcode,
                'is_purchase' => $request->has('base_is_purchase'),
                'is_sale' => $request->has('base_is_sale'),
            ]);

            $submittedUnitIds = [];
            if ($request->has('units') && is_array($request->units)) {
                foreach ($request->units as $u) {
                    if (isset($u['id'])) $submittedUnitIds[] = $u['id'];
                }
            }
            
            $product->units()->where('is_base_unit', false)->whereNotIn('id', $submittedUnitIds)->delete();

            if ($request->has('units') && is_array($request->units)) {
                foreach ($request->units as $index => $unitData) {
                    $uName = (!empty($unitData['name'])) ? $unitData['name'] : ($unitData['name_select'] ?? 'وحدة');
                    if($uName == 'custom') $uName = $unitData['name'] ?? 'وحدة';

                    $uFactor = (float)($unitData['factor'] ?? 1);
                    $uCost = $baseCost * $uFactor;

                    $data = [
                        'unit_name' => $uName,
                        'conversion_factor' => $uFactor,
                        'barcode' => $unitData['barcode'] ?? null,
                        'cost_price' => $uCost,
                        'selling_price' => (float)($unitData['selling_price'] ?? 0),
                        'profit_percent' => (float)($unitData['profit_percent'] ?? 0),
                        'is_base_unit' => false,
                        'is_purchase' => isset($unitData['is_purchase']),
                        'is_sale' => isset($unitData['is_sale']),
                        'product_id' => $product->id,
                    ];

                    if (isset($unitData['id'])) {
                        $existingUnit = ProductUnit::find($unitData['id']);
                        if ($existingUnit) {
                            $existingUnit->update($data);
                            if ($request->hasFile("units.$index.image")) {
                                $existingUnit->clearMediaCollection('unit_images');
                                $existingUnit->addMediaFromRequest("units.$index.image")->toMediaCollection('unit_images');
                            }
                        }
                    } else {
                        $newUnit = ProductUnit::create($data);
                        if ($request->hasFile("units.$index.image")) {
                            $newUnit->addMediaFromRequest("units.$index.image")->toMediaCollection('unit_images');
                        }
                    }
                }
            }

            // --- تحديث المكونات (Recipe) ---
            if ($product->product_type == 'meal') {
                $product->recipes()->delete(); // حذف القديم
                if ($request->has('recipe')) {
                    foreach ($request->recipe as $recipeData) {
                        if (!empty($recipeData['ingredient_id'])) {
                            $ing = Product::find($recipeData['ingredient_id']);
                            $product->recipes()->create([
                                'ingredient_product_id' => $recipeData['ingredient_id'],
                                'quantity' => $recipeData['quantity'] ?? 1,
                                'unit_id' => $ing->baseUnit ? $ing->baseUnit->id : null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('store.products.index')->with('success', 'تم تعديل المنتج بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    
    public function destroy(Product $product) { $product->delete(); return back()->with('success', 'تم الحذف'); }

   // =========================================================
    // 🔥 إدارة الدفعات (Batches) بدقة عالية 🔥
    // =========================================================

    // =========================================================
    // 🔥 إدارة الدفعات (Batches) المتقدمة - الإصدار الجديد 🔥
    // =========================================================

    public function expiredManager()
    {
        $storeId = Auth::user()->store_id;

        // جلب المنتجات التي لديها دفعات منتهية أو قريبة للانتهاء
        // سنقوم بتجميعها حسب المنتج لعرض الحالة الكاملة (منتهي، قريب، سليم)
        $productsWithIssues = Product::where('store_id', $storeId)
            ->whereHas('batches', function($q) {
                $q->where('quantity', '>', 0)
                  ->where('expiry_date', '<=', \Carbon\Carbon::now()->addDays(30));
            })
            ->with(['batches' => function($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
            }])
            ->get();

        // تجهيز بيانات العرض
        $productGroups = $productsWithIssues->map(function($product) {
            $batches = $product->batches;
            $today = \Carbon\Carbon::now()->startOfDay();
            $warningDate = $today->copy()->addDays($product->expiry_warning_days ?? 30);

            return [
                'product' => $product,
                'expired' => $batches->filter(fn($b) => $b->expiry_date < $today),
                'near_expiry' => $batches->filter(fn($b) => $b->expiry_date >= $today && $b->expiry_date <= $warningDate),
                'valid' => $batches->filter(fn($b) => $b->expiry_date > $warningDate),
                'total_stock' => $product->batches->sum('quantity'),
            ];
        });

        // 2. جلب المنتجات منخفضة المخزون
        $lowStockProducts = Product::where('store_id', $storeId)
            ->whereColumn('current_stock', '<=', 'alert_quantity')
            ->orderBy('current_stock', 'asc')
            ->get();

        return view('store_owner.products.expired_manager', compact('productGroups', 'lowStockProducts'));
    }

    // إتلاف (جزئي أو كلي)
    public function disposeStock(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'proof_image' => 'required|image|max:2048', // مطلوب للإثبات
            'reason' => 'required|string|max:255',
        ]);

        $batch = \App\Models\ProductBatch::findOrFail($request->batch_id);
        
        // ✅ التحقق من الكسور
        $prod = $batch->product;
        $uName = $prod->baseUnit ? $prod->baseUnit->unit_name : 'قطعة';
        if (!preg_match('/kilo|kg|كيلو|كغ/i', $uName) && fmod((float)$request->quantity, 1) !== 0.0) {
            return back()->with('error', "الوحدة ($uName) لا تقبل الكسور!");
        }
        
        if ($request->quantity > $batch->quantity) {
             return back()->with('error', 'الكمية المراد إتلافها أكبر من المتوفر في هذه الدفعة!');
        }

        DB::beginTransaction();
        try {
            // 1. خصم الكمية
            $batch->quantity -= $request->quantity;
            $batch->save();

            // 2. تحديث المنتج الرئيسي
            $product = $batch->product;
            $product->current_stock = $product->batches()->sum('quantity');
            $nextBatch = $product->batches()->where('quantity', '>', 0)->orderBy('expiry_date', 'asc')->first();
            $product->expiry_date = $nextBatch ? $nextBatch->expiry_date : null;
            $product->save();

            // 3. رفع الصورة
            $imagePath = $request->file('proof_image')->store('expiry_proofs', 'public');

            // 4. تسجيل العملية في السجل
            \App\Models\InventoryActionLog::create([
                'store_id' => $product->store_id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'user_id' => Auth::id(),
                'action' => 'dispose',
                'quantity' => $request->quantity,
                'old_date' => $batch->expiry_date,
                'reason' => $request->reason,
                'proof_image' => $imagePath,
            ]);

            DB::commit();
            return back()->with('success', "تم إتلاف {$request->quantity} قطعة بنجاح وتم تسجيل الإثبات.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    // تمديد/تصحيح (جزئي أو كلي)
    public function extendExpiry(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'new_date' => 'required|date|after:today',
            'proof_image' => 'required|image|max:2048', // مطلوب للإثبات
            'reason' => 'required|string|max:255',
        ]);

        $batch = \App\Models\ProductBatch::findOrFail($request->batch_id);

        // ✅ التحقق من الكسور
        $prod = $batch->product;
        $uName = $prod->baseUnit ? $prod->baseUnit->unit_name : 'قطعة';
        if (!preg_match('/kilo|kg|كيلو|كغ/i', $uName) && fmod((float)$request->quantity, 1) !== 0.0) {
            return back()->with('error', "الوحدة ($uName) لا تقبل الكسور!");
        }
        
        if ($request->quantity > $batch->quantity) {
            return back()->with('error', 'الكمية المحددة أكبر من المتوفر في الدفعة!');
        }

        DB::beginTransaction();
        try {
            $product = $batch->product;
            $originalDate = $batch->expiry_date;

            // السيناريو أ: الكمية كاملة -> تحديث الدفعة مباشرة
            if ($request->quantity == $batch->quantity) {
                $batch->expiry_date = $request->new_date;
                $batch->save();
            } 
            // السيناريو ب: جزء من الكمية -> تقسيم الدفعة
            else {
                // 1. خصم من القديم
                $batch->quantity -= $request->quantity;
                $batch->save();

                // 2. إنشاء دفعة جديدة بالتاريخ الجديد
                $newBatch = $product->batches()->create([
                    'sku' => $batch->sku, // نفس الباركود
                    'quantity' => $request->quantity,
                    'expiry_date' => $request->new_date,
                    'purchase_price' => $batch->purchase_price,
                    'supplier_id' => $batch->supplier_id,
                ]);
            }

            // تحديث المنتج الرئيسي
            $nextBatch = $product->batches()->where('quantity', '>', 0)->orderBy('expiry_date', 'asc')->first();
            $product->expiry_date = $nextBatch ? $nextBatch->expiry_date : null;
            $product->save();

            // رفع الصورة
            $imagePath = $request->file('proof_image')->store('expiry_proofs', 'public');

            // تسجيل العملية
            \App\Models\InventoryActionLog::create([
                'store_id' => $product->store_id,
                'product_id' => $product->id,
                'batch_id' => $batch->id, // نربطها بالدفعة الأصلية للتاريخ
                'user_id' => Auth::id(),
                'action' => 'extend_expiry',
                'quantity' => $request->quantity,
                'old_date' => $originalDate,
                'new_date' => $request->new_date,
                'reason' => $request->reason,
                'proof_image' => $imagePath,
            ]);

            DB::commit();
            return back()->with('success', "تم تصحيح تاريخ الصلاحية لـ {$request->quantity} قطعة بنجاح.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    
    // =========================================================
    
    public function checkBarcode(Request $request)
    {
        $barcode = $request->barcode;
        if (!$barcode) return response()->json(['exists' => false]);

        $exists = ProductUnit::whereHas('product', function($q) {
            $q->where('store_id', Auth::user()->store->id);
        })->where('barcode', $barcode)->first();

        if ($exists) {
            return response()->json([
                'exists' => true,
                'product_name' => $exists->product->name_ar,
                'product_id' => $exists->product_id
            ]);
        }
        return response()->json(['exists' => false]);
    }

   public function search(Request $request)
    {
        $term = $request->term; 
        $storeId = Auth::user()->store->id;

        $products = Product::where('store_id', $storeId)
            ->where(function($q) use ($term) {
                $q->where('name_ar', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%")
                  ->orWhereHas('units', function($q2) use ($term) {
                      $q2->where('barcode', 'LIKE', "%{$term}%");
                  });
            })
            ->with(['units' => function($q) {
                $q->select(
                    'id', 
                    'product_id', 
                    'unit_name', 
                    'conversion_factor', 
                    'purchase_price', 
                    'cost_price', 
                    'selling_price', 
                    'barcode', 
                    'is_base_unit'
                );
            }])
            ->take(20) 
            ->get();

        return response()->json($products);
    }

    // ==========================================
    // 🔥 الدوال الجديدة (الآن هي داخل الكلاس بشكل صحيح) 🔥
    // ==========================================

    // تحديث المخزون السريع (على مسؤولية المحرر)
    public function quickUpdateStock(Request $request)
    {
        $product = Product::where('id', $request->product_id)->where('store_id', Auth::user()->store->id)->firstOrFail();
        
        $oldStock = $product->current_stock;
        $product->current_stock = $request->new_stock;
        $product->save();

        return response()->json([
            'status' => 'success',
            'message' => "تم تحديث المخزون من " . (float)$oldStock . " إلى " . (float)$product->current_stock,
        ]);
    }

    // تحديث حد التنبيه السريع
    public function quickUpdateAlert(Request $request)
    {
        $product = Product::where('id', $request->product_id)->where('store_id', Auth::user()->store->id)->firstOrFail();
        
        $product->alert_quantity = $request->new_alert;
        $product->save();

        return response()->json([
            'status' => 'success',
            'message' => "تم تعديل حد التنبيه ليصبح " . (float)$product->alert_quantity,
        ]);
    }

} // ✅ هذا القوس هو نهاية الملف ويغلق الكلاس كاملاً