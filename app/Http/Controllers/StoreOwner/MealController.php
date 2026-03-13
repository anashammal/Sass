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
use App\Services\ExchangeRateService;

class MealController extends Controller
{
    public function index(Request $request)
    {
        // Enforce redirection for non-restaurants
        if (strtolower(Auth::user()->store->type) !== 'restaurant') {
            return redirect()->route('store.products.index');
        }

        $storeId = Auth::user()->store->id;
        $store = Auth::user()->store;
        $baseCurrency = $store->baseCurrency;
        
        $query = Product::where('store_id', $storeId)
            ->whereIn('product_type', ['meal', 'ingredient', 'compound', 'standard'])
            ->with(['baseUnit', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('units', function($q2) use ($search) {
                      $q2->where('barcode', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            // Handle handling "null" category if needed, but usually just filter by IDs.
            // If we want "Uncategorized", we'd need a special flag. For now just standard IDs.
             $query->whereIn('category_id', (array)$request->category_id);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->whereIn('product_type', (array)$request->type);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status);
        }

        if ($request->currency_id) {
            $cid = $request->currency_id;
            $baseId = $store->base_currency_id;
            
            $query->whereHas('units', function ($q) use ($cid, $baseId) {
                $q->where('is_base_unit', 1)
                  ->where(function($sq) use ($cid, $baseId) {
                      if ($cid == $baseId) {
                          $sq->where('sell_price_currency_id', $cid)
                             ->orWhereNull('sell_price_currency_id');
                      } else {
                          $sq->where('sell_price_currency_id', $cid);
                      }
                  });
            });
        }

        $products = $query->latest()->paginate($request->input('per_page', 10))->withQueryString();

        // 🟢 جلب العملات المتاحة للمتجر
        $currencies = $store->acceptedCurrencies;

        $prodStats = [
            'total' => Product::where('store_id', $storeId)->whereIn('product_type', ['meal', 'ingredient', 'compound', 'standard'])->count(),
            'meals' => Product::where('store_id', $storeId)->where('product_type', 'meal')->count(),
            'standards' => Product::where('store_id', $storeId)->where('product_type', 'standard')->count(),
            'ingredients' => Product::where('store_id', $storeId)->where('product_type', 'ingredient')->count(),
            'compounds' => Product::where('store_id', $storeId)->where('product_type', 'compound')->count(),
            'low_stock' => Product::where('store_id', $storeId)
                            ->whereIn('product_type', ['meal', 'ingredient', 'standard']) // Maybe not compound if it's auto?
                            ->whereColumn('current_stock', '<=', 'alert_quantity') 
                            ->where('current_stock', '>', 0)
                            ->count(),
        ];

        $categories = Category::where('store_id', $storeId)->get();
        $store = Auth::user()->store;

        if ($request->ajax()) {
            return view('store_owner.meals.partials.table_rows', compact('products', 'baseCurrency', 'currencies'))->render();
        }

        return view('store_owner.meals.index', compact('products', 'prodStats', 'categories', 'baseCurrency', 'currencies'));
    }

    public function getIngredientsJson(Request $request)
    {
        $query = Product::where('store_id', Auth::user()->store->id)
            ->whereIn('product_type', ['standard', 'ingredient', 'compound'])
            ->with('units');

        if ($request->has('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            });
        }
        
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        $ingredients = $query->limit(20)->get()
            ->map(function($ing) {
                return [
                    'id' => $ing->id,
                    'name' => $ing->name,
                    'barcode' => $ing->sku,
                    'units' => $ing->units->map(function($u) {
                        return [
                            'id' => $u->id,
                            'unit_name' => $u->unit_name,
                            'cost_price' => $u->cost_price,
                            'is_base_unit' => $u->is_base_unit,
                        ];
                    })
                ];
            });
            
        return response()->json($ingredients);
    }

    public function create() 
    { 
        if (strtolower(Auth::user()->store->type) !== 'restaurant') {
            return redirect()->route('store.products.index');
        }

        $store = Auth::user()->store; 
        $categories = Category::where('store_id', $store->id)->get(); 
        $taxRates = explode(',', $store->tax_rates ?? '0,15');
        
        $ingredients = Product::where('store_id', $store->id)
                ->whereIn('product_type', ['standard', 'ingredient', 'compound']) // Allow Compound
                ->with('units')
                ->get();

        $acceptedCurrencies = $store->acceptedCurrencies()->get();
        $baseCurrency = $store->baseCurrency;
        
        if ($baseCurrency && !$acceptedCurrencies->contains('id', $baseCurrency->id)) {
            $acceptedCurrencies->prepend($baseCurrency);
        }
        
        $currenciesData = [];
        if ($baseCurrency) {
            $exchangeService = app(\App\Services\ExchangeRateService::class);
            foreach ($acceptedCurrencies as $cur) {
                if ($cur->id === $baseCurrency->id) continue;
                $rate = $cur->pivot->custom_rate ?? ($exchangeService->getExchangeRate($cur->code, $baseCurrency->code) ?? 1);
                $currenciesData[$cur->id] = (float)$rate;
            }
        }

        return view('store_owner.meals.create', compact('categories', 'store', 'taxRates', 'ingredients', 'acceptedCurrencies', 'currenciesData', 'baseCurrency')); 
    }

    public function store(Request $request) 
    {
        if (empty($request->base_unit_name) && $request->filled('base_unit_select')) {
            $request->merge(['base_unit_name' => $request->base_unit_select]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable',
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
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'sku' => $barcode,
                'alert_quantity' => $request->alert_quantity ?? 0,
                'expiry_warning_days' => $request->expiry_warning_days ?? 0,
                'tax_percent' => $request->tax_percent ?? 0,
                'is_active' => $request->has('is_active'),
                'product_type' => $request->product_type ?? 'meal',
            ]);

            if ($request->hasFile('base_unit_image')) {
                $product->addMediaFromRequest('base_unit_image')->toMediaCollection('products');
            }

            $subUnitCount = (float)($request->pieces_per_unit ?? 1);
            $purchasePrice = (float)$request->purchase_price;

            // Resolve Base Unit Name
            $baseUnitResolved = ($request->base_unit_select === 'custom') ? $request->base_unit_name : $request->base_unit_select;
            if (empty($baseUnitResolved)) $baseUnitResolved = 'وحدة';

            $purchaseRate = (float)($request->purchase_exchange_rate ?? 1);
            $sellRate = (float)($request->base_sell_exchange_rate ?? 1);

            $purchasePriceInBase = $purchasePrice * $purchaseRate;
            $sellingPriceInBase = ((float)$request->base_selling_price) * $sellRate;

            // If Count > 1, create Base Unit (Small) AND Purchase Unit (Big)
            if ($subUnitCount > 1 && !empty($request->sub_unit_name)) {
                
                $baseUnitSellingPriceInBase = ($subUnitCount > 0) ? ($sellingPriceInBase / $subUnitCount) : 0;
                $baseCostInBase = ($subUnitCount > 0) ? ($purchasePriceInBase / $subUnitCount) : 0;
                
                // 1. Create Base Unit (Smallest, e.g. Loaf)
                $product->units()->create([
                    'unit_name' => $request->sub_unit_name, 
                    'conversion_factor' => 1,
                    'purchase_price' => $baseCostInBase, // Base price is in base currency
                    'cost_price' => $baseCostInBase,
                    'selling_price' => $baseUnitSellingPriceInBase, 
                    'profit_percent' => (float)$request->base_profit_percent,
                    'barcode' => $barcode,
                    'is_base_unit' => true,
                    'is_purchase' => false, 
                    'is_sale' => false, 
                ]);

                // 2. Create Purchase Unit (Main, e.g. Bag)
                $product->units()->create([
                    'unit_name' => $baseUnitResolved,
                    'conversion_factor' => $subUnitCount,
                    'purchase_price' => $purchasePrice,
                    'purchase_price_currency_id' => $request->purchase_price_currency_id,
                    'purchase_exchange_rate' => $purchaseRate,
                    'cost_price' => $purchasePriceInBase,
                    'selling_price' => (float)$request->base_selling_price,
                    'sell_price_currency_id' => $request->base_selling_price_currency_id,
                    'sell_exchange_rate' => $sellRate,
                    'profit_percent' => (float)$request->base_profit_percent,
                    'is_base_unit' => false,
                    'is_purchase' => $request->has('base_is_purchase'), 
                    'is_sale' => $request->has('base_is_sale'), 
                ]);
                
                $baseCost = $baseCostInBase; // For following auto-link logic

            } else {
                // Normal Single Unit (Bottle, Kg, Piece...)
                $product->units()->create([
                    'unit_name' => $baseUnitResolved,
                    'conversion_factor' => 1,
                    'purchase_price' => $purchasePrice,
                    'purchase_price_currency_id' => $request->purchase_price_currency_id,
                    'purchase_exchange_rate' => $purchaseRate,
                    'cost_price' => $purchasePriceInBase,
                    'selling_price' => (float)$request->base_selling_price,
                    'sell_price_currency_id' => $request->base_selling_price_currency_id,
                    'sell_exchange_rate' => $sellRate,
                    'profit_percent' => (float)$request->base_profit_percent,
                    'barcode' => $barcode,
                    'is_base_unit' => true,
                    'is_purchase' => $request->has('base_is_purchase'), 
                    'is_sale' => $request->has('base_is_sale'),
                ]);
                $baseCost = $purchasePriceInBase;
            }

            // --- Auto-Link Kg/Gram Logic ---
            $baseNameLower = trim($baseUnitResolved); // Case sensitive for Arabic might be safer, but just trim.
            $isKg = in_array(strtolower($baseNameLower), ['كيلوغرام', 'kg', 'kilo', 'كيلو']) || $baseNameLower === 'كيلوغرام';
            $isGram = in_array(strtolower($baseNameLower), ['غرام', 'gram', 'g']) || $baseNameLower === 'غرام';
            $isLiter = in_array(strtolower($baseNameLower), ['ليتر', 'liter', 'l']) || $baseNameLower === 'ليتر';
            $isMl = in_array(strtolower($baseNameLower), ['مل', 'ml', 'milliliter']) || $baseNameLower === 'مل';

            $baseIsPurchase = $request->has('base_is_purchase');
            $baseIsSale = $request->has('base_is_sale');

            if ($isKg) {
                // Base is Kg -> Create/Update Gram (Factor 0.001)
                $product->units()->updateOrCreate(
                    ['unit_name' => 'غرام', 'product_id' => $product->id],
                    [
                        'conversion_factor' => 0.001,
                        'purchase_price' => $baseCost * 0.001,
                        'cost_price' => $baseCost * 0.001,
                        'selling_price' => ($sellingPriceInBase) * 0.001,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            } elseif ($isGram) {
                // Base is Gram -> Create/Update Kg (Factor 1000)
                $product->units()->updateOrCreate(
                    ['unit_name' => 'كيلوغرام', 'product_id' => $product->id],
                    [
                        'conversion_factor' => 1000,
                        'purchase_price' => $baseCost * 1000,
                        'cost_price' => $baseCost * 1000,
                        'selling_price' => ($sellingPriceInBase) * 1000,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            } elseif ($isLiter) {
                // Base is Liter -> Create/Update ml (Factor 0.001)
                $product->units()->updateOrCreate(
                    ['unit_name' => 'مل', 'product_id' => $product->id],
                    [
                        'conversion_factor' => 0.001,
                        'purchase_price' => $baseCost * 0.001,
                        'cost_price' => $baseCost * 0.001,
                        'selling_price' => ((float)$request->base_selling_price) * 0.001,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            } elseif ($isMl) {
                // Base is ml -> Create/Update Liter (Factor 1000)
                $product->units()->updateOrCreate(
                    ['unit_name' => 'ليتر', 'product_id' => $product->id],
                    [
                        'conversion_factor' => 1000,
                        'purchase_price' => $baseCost * 1000,
                        'cost_price' => $baseCost * 1000,
                        'selling_price' => ((float)$request->base_selling_price) * 1000,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            }
            // --------------------------------

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
                    $uPurchaseRate = (float)($unitData['purchase_exchange_rate'] ?? 1);
                    $uSellRate = (float)($unitData['sell_exchange_rate'] ?? 1);
                    
                    $uCostInBaseCurrency = $baseCost * $uFactor;
                    $uSellingPriceInBaseCurrency = (float)($unitData['selling_price'] ?? 0) * $uSellRate;

                    $extraUnit = $product->units()->create([
                        'unit_name' => $uName,
                        'conversion_factor' => $uFactor,
                        'barcode' => $uBarcode,
                        'purchase_price' => (float)($unitData['purchase_price'] ?? 0),
                        'purchase_price_currency_id' => $unitData['purchase_price_currency_id'] ?? null,
                        'purchase_exchange_rate' => $uPurchaseRate,
                        'cost_price' => $uCostInBaseCurrency,
                        'selling_price' => $uSellingPriceInBaseCurrency,
                        'sell_price_currency_id' => $unitData['sell_price_currency_id'] ?? null,
                        'sell_exchange_rate' => $uSellRate,
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

            // Recipe logic (For Meals AND Compound Ingredients)
            if (in_array($product->product_type, ['meal', 'compound']) && $request->has('recipe')) {
                foreach ($request->recipe as $recipeData) {
                    if (!empty($recipeData['ingredient_id'])) {
                        $ing = Product::find($recipeData['ingredient_id']);
                        $product->recipes()->create([
                            'ingredient_product_id' => $recipeData['ingredient_id'],
                            'quantity' => $recipeData['quantity'] ?? 1,
                            'unit_id' => $recipeData['unit_id'] ?? ($ing->baseUnit ? $ing->baseUnit->id : null),
                        ]);
                    }
                }
                $product->recalculateMealCost();
            }

            DB::commit();

            if (request('iframe') && request('quick_add')) {
                return view('store_owner.meals.partials.quick_add_success', [
                    'product_id' => $product->id,
                    'product_name' => $product->name
                ]);
            }

            return redirect()->route('store.meals.index')->with('success', 'تم حفظ الوجبة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }

    public function edit(Product $meal) { 
        if (strtolower(Auth::user()->store->type) !== 'restaurant') {
            return redirect()->route('store.products.index');
        }

        if ($meal->store_id !== Auth::user()->store->id) abort(403);
        $store = Auth::user()->store;
        $categories = Category::where('store_id', $store->id)->get();
        $taxRates = explode(',', $store->tax_rates ?? '0,15');
        
        $ingredients = Product::where('store_id', $store->id)
                ->whereIn('product_type', ['standard', 'ingredient', 'compound']) // Allow Compound as Ingredient
                ->with('units')
                ->get();
        $meal->load('recipes.ingredient.units');

        $exchangeService = app(ExchangeRateService::class);
        $baseCurrency = $store->baseCurrency;
        $acceptedCurrencies = $store->acceptedCurrencies()->withPivot('custom_rate')->get();
        
        if ($baseCurrency && !$acceptedCurrencies->contains('id', $baseCurrency->id)) {
            $acceptedCurrencies->prepend($baseCurrency);
        }
        $currenciesData = [];
        if ($baseCurrency) {
            $currenciesData[$baseCurrency->id] = 1.0; // Base currency rate is 1
            foreach ($acceptedCurrencies as $cur) {
                if ($cur->id === $baseCurrency->id) continue;
                $rate = $cur->pivot->custom_rate ?? ($exchangeService->getExchangeRate($cur->code, $baseCurrency->code) ?? 1);
                $currenciesData[$cur->id] = (float)$rate;
            }
        }

        return view('store_owner.meals.edit', compact('meal', 'categories', 'store', 'taxRates', 'ingredients', 'acceptedCurrencies', 'currenciesData', 'baseCurrency'));
    }

    public function update(Request $request, Product $meal)
    {
        if ($meal->store_id !== Auth::user()->store->id) abort(403);

        if ($request->filled('base_unit_select') && $request->base_unit_select !== 'custom') {
            $request->merge(['base_unit_name' => $request->base_unit_select]);
        }

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'category_id' => 'nullable', 
            'base_unit_name' => 'required',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $meal->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'sku' => $request->base_barcode, 
                'alert_quantity' => $request->alert_quantity ?? 0,
                'expiry_warning_days' => $request->expiry_warning_days ?? 0,
                'tax_percent' => $request->tax_percent ?? 0,
                'is_active' => $request->has('is_active'),
                'product_type' => $request->product_type ?? 'meal',
            ]);

            if ($request->hasFile('base_unit_image')) {
                $meal->clearMediaCollection('products');
                $meal->addMediaFromRequest('base_unit_image')->toMediaCollection('products');
            }

            $subUnitCount = (float)($request->pieces_per_unit ?? 1);
            $purchasePrice = (float)$request->purchase_price;
            $purchaseRate = (float)($request->purchase_exchange_rate ?? 1);
            $totalPurchaseCostInBase = $purchasePrice * $purchaseRate;
            $baseCost = ($subUnitCount > 0) ? ($totalPurchaseCostInBase / $subUnitCount) : 0;

            // Update the main base unit (which might be the purchase unit if subUnitCount > 1, or the single unit)
            $purchaseRate = (float)($request->purchase_exchange_rate ?? 1);
            $sellRate = (float)($request->base_sell_exchange_rate ?? 1);
            $baseCostInBaseCurrency = ($purchasePrice * $purchaseRate);
            $sellingPriceInBaseCurrency = (float)$request->base_selling_price * $sellRate;

            $meal->baseUnit()->update([
                'unit_name' => $request->base_unit_name,
                'conversion_factor' => $subUnitCount, // This is the conversion factor for the main unit
                'purchase_price' => $purchasePrice,
                'purchase_price_currency_id' => $request->purchase_price_currency_id,
                'purchase_exchange_rate' => $request->purchase_exchange_rate,
                'cost_price' => $purchasePrice * $purchaseRate, // Cost price for the main unit
                'selling_price' => $sellingPriceInBaseCurrency,
                'sell_price_currency_id' => $request->base_selling_price_currency_id,
                'sell_exchange_rate' => $request->base_sell_exchange_rate,
                'profit_percent' => (float)$request->base_profit_percent,
                'barcode' => $request->base_barcode,
                'is_purchase' => $request->has('base_is_purchase'),
                'is_sale' => $request->has('base_is_sale'),
            ]);

            // Handle Sub-Unit Name Update if exists (this is the smallest unit, conversion_factor = 1)
            if ($subUnitCount > 1 && $request->filled('sub_unit_name')) {
                // If there's a sub-unit (conversion 1), update it
                $subUnit = $meal->units()->where('conversion_factor', 1)->where('is_base_unit', true)->first();
                if ($subUnit) {
                    $subUnit->update([
                        'unit_name' => $request->sub_unit_name,
                        'purchase_price' => $baseCost,
                        'cost_price' => $baseCost,
                        'selling_price' => ($subUnitCount > 0) ? ((float)$request->base_selling_price / $subUnitCount) : 0,
                        'is_purchase' => false,
                        'is_sale' => false,
                    ]);
                } else {
                    // Create it if missed
                     $meal->units()->create([
                        'unit_name' => $request->sub_unit_name, 
                        'conversion_factor' => 1,
                        'purchase_price' => $baseCost,
                        'cost_price' => $baseCost,
                        'selling_price' => ($subUnitCount > 0) ? ((float)$request->base_selling_price / $subUnitCount) : 0,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => true,
                        'is_purchase' => false, 
                        'is_sale' => false,
                    ]);
                }
            }

            // Sync other units
            $submittedUnitIds = [];
            if ($request->has('units') && is_array($request->units)) {
                foreach ($request->units as $u) {
                    if (isset($u['id'])) $submittedUnitIds[] = $u['id'];
                }
            }
            // Do not delete auto-generated units (check logic if needed but simple delete except submitted is tricky if we auto-gen)
            // For now, standard delete works manually.
            $meal->units()->where('is_base_unit', false)->whereNotIn('id', $submittedUnitIds)->delete();

            $baseUnitResolved = $request->base_unit_name; 
            $baseNameLower = trim($baseUnitResolved);
            $isKg = in_array(strtolower($baseNameLower), ['كيلوغرام', 'kg', 'kilo', 'كيلو']) || $baseNameLower === 'كيلوغرام';
            $isGram = in_array(strtolower($baseNameLower), ['غرام', 'gram', 'g']) || $baseNameLower === 'غرام';
            $isLiter = in_array(strtolower($baseNameLower), ['ليتر', 'liter', 'l']) || $baseNameLower === 'ليتر';
            $isMl = in_array(strtolower($baseNameLower), ['مل', 'ml', 'milliliter']) || $baseNameLower === 'مل';

            $baseIsPurchase = $request->has('base_is_purchase');
            $baseIsSale = $request->has('base_is_sale');

            if ($isKg) {
                $meal->units()->updateOrCreate(
                    ['unit_name' => 'غرام', 'product_id' => $meal->id],
                    [
                        'conversion_factor' => 0.001,
                        'purchase_price' => $baseCost * 0.001,
                        'cost_price' => $baseCost * 0.001,
                        'selling_price' => ((float)$request->base_selling_price) * 0.001,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            } elseif ($isGram) {
                $meal->units()->updateOrCreate(
                    ['unit_name' => 'كيلوغرام', 'product_id' => $meal->id],
                    [
                        'conversion_factor' => 1000,
                        'purchase_price' => $baseCost * 1000,
                        'cost_price' => $baseCost * 1000,
                        'selling_price' => ((float)$request->base_selling_price) * 1000,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            } elseif ($isLiter) {
                $meal->units()->updateOrCreate(
                    ['unit_name' => 'مل', 'product_id' => $meal->id],
                    [
                        'conversion_factor' => 0.001,
                        'purchase_price' => $baseCost * 0.001,
                        'cost_price' => $baseCost * 0.001,
                        'selling_price' => ((float)$request->base_selling_price) * 0.001,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            } elseif ($isMl) {
                $meal->units()->updateOrCreate(
                    ['unit_name' => 'ليتر', 'product_id' => $meal->id],
                    [
                        'conversion_factor' => 1000,
                        'purchase_price' => $baseCost * 1000,
                        'cost_price' => $baseCost * 1000,
                        'selling_price' => ((float)$request->base_selling_price) * 1000,
                        'profit_percent' => (float)$request->base_profit_percent,
                        'is_base_unit' => false,
                        'is_purchase' => $baseIsPurchase,
                        'is_sale' => $baseIsSale
                    ]
                );
            }
            // --------------------------------

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
                        'product_id' => $meal->id,
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

            // Sync recipe
            if (in_array($meal->product_type, ['meal', 'compound'])) {
                $meal->recipes()->delete();
                if ($request->has('recipe')) {
                    foreach ($request->recipe as $recipeData) {
                        if (!empty($recipeData['ingredient_id'])) {
                            $ing = Product::find($recipeData['ingredient_id']);
                            $meal->recipes()->create([
                                'ingredient_product_id' => $recipeData['ingredient_id'],
                                'quantity' => $recipeData['quantity'] ?? 1,
                                'unit_id' => $recipeData['unit_id'] ?? ($ing->baseUnit ? $ing->baseUnit->id : null),
                            ]);
                        }
                    }
                }
                $meal->recalculateMealCost();
            }

            DB::commit();

            if (request('iframe') && request('quick_add')) {
                return view('store_owner.meals.partials.quick_add_success', [
                    'product_id' => $meal->id,
                    'product_name' => $meal->name
                ]);
            }

            return redirect()->route('store.meals.index')->with('success', 'تم تعديل الوجبة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function recalculateAll() {
        if (Auth::user()->store_id !== Auth::user()->store->id) abort(403);
        $count = Product::recalculateAllMeals();
        return back()->with('success', "تم تحديث تكاليف $count أصناف بنجاح.");
    }

    public function destroy(Product $meal) { $meal->delete(); return back()->with('success', 'تم الحذف'); }
}
