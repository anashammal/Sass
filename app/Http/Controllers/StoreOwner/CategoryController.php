<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // <-- !! قمنا بإضافة هذا للتحقق المتقدم !!

class CategoryController extends Controller
{
    // (دالة index كما هي - لا تغيير)
    public function index()
    {
        $store = Auth::user()->store;
        $categories = Category::where('store_id', $store->id)
                                ->whereNull('parent_id')
                                ->with('children')
                                ->orderBy('name')
                                ->get();
        // نحتاج قائمة مسطحة للمودال (نقل المنتجات)
        $allCategories = Category::where('store_id', $store->id)->orderBy('name')->get(['id', 'name']);
        
        return view('store_owner.categories.index', compact('categories', 'allCategories'));
    }

    // (دالة create كما هي - لا تغيير)
    public function create()
    {
        $store = Auth::user()->store;
        $allCategories = Category::where('store_id', $store->id)
                                    ->orderBy('name')
                                    ->get();
        return view('store_owner.categories.create', compact('allCategories'));
    }

    // (دالة store كما هي - لا تغيير)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);
        $store_id = Auth::user()->store->id;
        $category = new Category();
        $category->name = $validatedData['name'];
        $category->store_id = $store_id;
        $category->parent_id = $validatedData['parent_id'];
        $category->save();
        return redirect()->route('store.categories.index')
                         ->with('success', 'تم إنشاء التصنيف بنجاح!');
    }

    // (دالة show فارغة كما هي)
    public function show(Category $category) {}


    /**
     * !! -- هذا هو الكود الجديد (دالة عرض فورم التعديل) -- !!
     */
    public function edit(Category $category)
    {
        // 1. التأكد أن هذا التصنيف يخص المتجر الحالي (للحماية)
        if ($category->store_id != Auth::user()->store->id) {
            abort(403, 'غير مصرح لك');
        }

        // 2. جلب "كل" التصنيفات الأخرى (لعرضها في قائمة "الأب")
        $allCategories = Category::where('store_id', Auth::user()->store->id)
                                    ->orderBy('name')
                                    ->get();

        // 3. عرض الواجهة (View) وإرسال التصنيف المُراد تعديله + كل التصنيفات
        return view('store_owner.categories.edit', compact('category', 'allCategories'));
    }

    /**
     * !! -- هذا هو الكود الجديد (دالة حفظ التحديث) -- !!
     */
    public function update(Request $request, Category $category)
    {
        // 1. التأكد أن هذا التصنيف يخص المتجر الحالي (للحماية)
        if ($category->store_id != Auth::user()->store->id) {
            abort(403, 'غير مصرح لك');
        }

        // 2. التحقق من البيانات
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [ // قواعد التحقق للأب
                'nullable', // يمكن أن يكون فارغاً (ليصبح تصنيف رئيسي)
                'integer',
                'exists:categories,id', // يجب أن يكون موجوداً في قاعدة البيانات
                Rule::notIn([$category->id]), // !! الأهم: لا يمكن للتصنيف أن يكون "أب" لنفسه !!
            ],
        ]);

        // 3. تحديث البيانات
        $category->name = $validatedData['name'];
        $category->parent_id = $validatedData['parent_id'];
        $category->save();

        // 4. إعادة التوجيه إلى صفحة "قائمة التصنيفات" مع رسالة نجاح
        return redirect()->route('store.categories.index')
                         ->with('success', 'تم تحديث التصنيف بنجاح!');
    }


    /**
     * !! -- دالة الحذف (كما هي) -- !!
     */
    public function destroy(Category $category)
    {
        if ($category->store_id != Auth::user()->store->id) {
            abort(403, 'غير مصرح لك');
        }

        if ($category->children->isNotEmpty()) {
            return redirect()->route('store.categories.index')
                             ->with('error', 'لا يمكن حذف هذا التصنيف لأنه يحتوي على تصنيفات فرعية. يجب حذف الأبناء أولاً.');
        }

        $category->delete();

        return redirect()->route('store.categories.index')
                         ->with('success', 'تم حذف التصنيف بنجاح!');
    }
    /**
     * التحقق من حالة التصنيف قبل الحذف
     */
    public function checkStatus(Category $category)
    {
        if ($category->store_id != Auth::user()->store->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $productsCount = \App\Models\Product::where('category_id', $category->id)->count();
        $hasChildren = $category->children()->exists();

        return response()->json([
            'products_count' => $productsCount,
            'has_children' => $hasChildren,
        ]);
    }

    /**
     * نقل المنتجات لتصنيف آخر ثم حذف التصنيف الحالي
     */
    public function moveProductsAndDelete(Request $request, Category $category)
    {
        if ($category->store_id != Auth::user()->store->id) abort(403);

        $request->validate([
            'target_category_id' => 'required|exists:categories,id|not_in:'.$category->id,
        ]);

        $targetCat = Category::find($request->target_category_id);
        if($targetCat->store_id != Auth::user()->store->id) abort(403);

        // نقل المنتجات
        \App\Models\Product::where('category_id', $category->id)
                           ->update(['category_id' => $targetCat->id]);

        // حذف التصنيف
        $category->delete();

        return response()->json(['success' => true, 'message' => 'تم نقل المنتجات وحذف التصنيف بنجاح']);
    }

    /**
     * حذف التصنيف مع جميع منتجاته (الحذف الإجباري)
     */
    public function forceDelete(Category $category)
    {
        if ($category->store_id != Auth::user()->store->id) abort(403);

        // حذف جميع المنتجات التابعة لهذا التصنيف
        $products = \App\Models\Product::where('category_id', $category->id)->get();
        foreach($products as $product) {
            // يمكن هنا إضافة منطق لحذف الصور أو الملفات المرتبطة بالمنتج إذا لزم الأمر
            $product->delete();
        }

        // حذف التصنيف
        $category->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف التصنيف وجميع منتجاته بنجاح']);
    }
}
