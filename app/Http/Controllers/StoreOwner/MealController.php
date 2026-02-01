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

class MealController extends Controller
{
    public function index(Request $request)
    {
        // Enforce redirection for non-restaurants
        if (strtolower(Auth::user()->store->type) !== 'restaurant') {
            return redirect()->route('store.products.index');
        }

        $storeId = Auth::user()->store->id;
        $query = Product::where('store_id', $storeId)
            ->whereIn('product_type', ['meal', 'ingredient'])
            ->with(['baseUnit', 'category']);

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
            'total' => Product::where('store_id', $storeId)->whereIn('product_type', ['meal', 'ingredient'])->count(),
            'low_stock' => Product::where('store_id', $storeId)
                            ->whereIn('product_type', ['meal', 'ingredient'])
                            ->whereColumn('current_stock', '<=', 'alert_quantity') 
                            ->where('current_stock', '>', 0)
                            ->count(),
        ];

        $categories = Category::where('store_id', $storeId)->get();
        $store = Auth::user()->store;

        if ($request->ajax()) {
            return view('store_owner.meals.partials.table_rows', compact('products', 'store'))->render();
        }

        return view('store_owner.meals.index', compact('products', 'prodStats', 'categories', 'store'));
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
                ->whereIn('product_type', ['standard', 'ingredient'])
                ->with('units')
                ->get();

        return view('store_owner.meals.create', compact('categories', 'store', 'taxRates', 'ingredients')); 
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
                'product_type' => $request->product_type ?? 'meal',
            ]);

            if ($request->hasFile('base_unit_image')) {
                $product->addMediaFromRequest('base_unit_image')->toMediaCollection('products');
            }

            $subUnitCount = (float)($request->sub_unit_count ?? 1);
            $purchasePrice = (float)$request->purchase_price;

            // If Count > 1, create Base Unit (Small) AND Purchase Unit (Big)
            if ($subUnitCount > 1 && !empty($request->sub_unit_name)) {
                // 1. Create Base Unit (Smallest, e.g. Loaf)
                $baseCost = $purchasePrice / $subUnitCount;
                $product->units()->create([
                    'unit_name' => $request->sub_unit_name, 
                    'conversion_factor' => 1,
                    'purchase_price' => $baseCost,
                    'cost_price' => $baseCost,
                    'selling_price' => (float)$request->base_selling_price, // Assuming selling price is for the base unit
                    'profit_percent' => (float)$request->base_profit_percent,
                    'barcode' => $barcode,
                    'is_base_unit' => true,
                    'is_purchase' => false, 
                    'is_sale' => $request->has('base_is_sale'),
                ]);

                // 2. Create Purchase Unit (Main, e.g. Bag)
                $product->units()->create([
                    'unit_name' => $request->base_unit_name,
                    'conversion_factor' => $subUnitCount,
                    'purchase_price' => $purchasePrice,
                    'cost_price' => $purchasePrice,
                    'selling_price' => (float)$request->base_selling_price * $subUnitCount, // Logic guess: sell bag?
                    'profit_percent' => (float)$request->base_profit_percent,
                    'is_base_unit' => false,
                    'is_purchase' => $request->has('base_is_purchase'), // Purchase is on the BIG unit
                    'is_sale' => $request->has('base_is_sale'), // Maybe sell big unit too
                ]);

            } else {
                // Default: Single Unit (Base = Purchase)
                $factor = (float)($request->pieces_per_unit ?? 1);
                $baseCost = ($factor > 0) ? ($purchasePrice / $factor) : 0; // Legacy support or just 1

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
            }

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

            // Recipe logic
            if ($product->product_type == 'meal' && $request->has('recipe')) {
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
                ->whereIn('product_type', ['standard', 'ingredient'])
                ->with('units')
                ->get();
        $meal->load('recipes.ingredient.units');

        return view('store_owner.meals.edit', compact('meal', 'categories', 'store', 'taxRates', 'ingredients'));
    }

    public function update(Request $request, Product $meal)
    {
        if ($meal->store_id !== Auth::user()->store->id) abort(403);

        if ($request->filled('base_unit_select') && $request->base_unit_select !== 'custom') {
            $request->merge(['base_unit_name' => $request->base_unit_select]);
        }

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'category_id' => 'required',
            'base_unit_name' => 'required',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $meal->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'sku' => $request->base_barcode, 
                'alert_quantity' => $request->alert_quantity,
                'expiry_warning_days' => $request->expiry_warning_days,
                'tax_percent' => $request->tax_percent ?? 0,
                'is_active' => $request->has('is_active'),
                'product_type' => $request->product_type ?? 'meal',
            ]);

            if ($request->hasFile('base_unit_image')) {
                $meal->clearMediaCollection('products');
                $meal->addMediaFromRequest('base_unit_image')->toMediaCollection('products');
            }

            $factor = (float)($request->pieces_per_unit ?? 1);
            $purchasePrice = (float)$request->purchase_price;
            $baseCost = ($factor > 0) ? ($purchasePrice / $factor) : 0;

            $meal->baseUnit()->update([
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

            // Sync other units
            $submittedUnitIds = [];
            if ($request->has('units') && is_array($request->units)) {
                foreach ($request->units as $u) {
                    if (isset($u['id'])) $submittedUnitIds[] = $u['id'];
                }
            }
            $meal->units()->where('is_base_unit', false)->whereNotIn('id', $submittedUnitIds)->delete();

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
            if ($meal->product_type == 'meal') {
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
            return redirect()->route('store.meals.index')->with('success', 'تم تعديل الوجبة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function destroy(Product $meal) { $meal->delete(); return back()->with('success', 'تم الحذف'); }
}
