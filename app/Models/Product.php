<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; 
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    // ✅ هذا السطر يعني: اسمح بتعديل كل الحقول (بما فيها expiry_date) ما عدا الـ id
    protected $guarded = ['id'];
    protected $appends = ['image_url'];

    protected $casts = [
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
        'quantity' => 'decimal:2',
        'alert_quantity' => 'decimal:2',
        'current_stock' => 'decimal:4', 
        'last_cost_price' => 'decimal:4',
        'tax_percent' => 'decimal:2',
        
        'expiry_date' => 'date', 
        'expiry_warning_days' => 'integer', // ✅ تمت الإضافة هنا
    ];

    // --- العلاقات الأساسية ---

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function baseUnit() 
    { 
        return $this->hasOne(ProductUnit::class)->where('is_base_unit', true); 
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class)->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class, 'parent_product_id');
    }

    public function ingredientIn()
    {
        return $this->hasMany(ProductRecipe::class, 'ingredient_product_id');
    }

    // =========================================================
    // 🔥 الدوال المساعدة 🔥
    // =========================================================

    // ✅ دالة حساب المخزون حسب الوحدة
    public function getStockByUnit($unit_id)
    {
        $unit = $this->units->where('id', $unit_id)->first();
        
        if (!$unit) return 0;

        $stock = $this->current_stock ?? 0;

        if ($unit->is_base_unit) return (float)$stock; 

        if ($unit->conversion_factor > 0) {
            return floor($stock / $unit->conversion_factor);
        }
        return 0;
    }

    // ✅ دالة إجمالي الكمية الصالحة
    public function getValidStockAttribute()
    {
        // إذا كنت ستستخدم الباتشات مستقبلاً
        return $this->batches()->whereDate('expiry_date', '>', now())->sum('quantity');
    }
    
    // ✅ خاصية افتراضية لفحص انتهاء الصلاحية للمنتج نفسه
    public function getIsExpiredAttribute()
    {
        if(!$this->expiry_date) return false;
        return \Carbon\Carbon::parse($this->expiry_date)->isPast();
    }

    /**
     * 🔥 إعادة حساب تكلفة الوجبة بناءً على أسعار الخامات (المكونات)
     */
    public function recalculateMealCost()
    {
        if ($this->product_type !== 'meal' && $this->product_type !== 'compound') return 0;

        // Force reload relations to ensure we have latest saved recipe data from DB
        $this->load(['recipes.ingredient.units', 'recipes.unit']);
        
        $totalCost = 0;
        foreach ($this->recipes as $recipe) {
            $ingredient = $recipe->ingredient;
            if (!$ingredient) continue;

            $unitCost = 0;
            // Priority 1: Use specific unit selected in recipe
            if ($recipe->unit) {
                $unitCost = (float)$recipe->unit->cost_price;
            } 
            // Priority 2: Fallback to base unit of the ingredient
            elseif ($ingredient->baseUnit) {
                $unitCost = (float)$ingredient->baseUnit->cost_price;
            }

            $totalCost += ($recipe->quantity * $unitCost);
        }

        // Update the basic unit assigned to this meal
        if ($this->baseUnit) {
            $sellingPrice = (float)$this->baseUnit->selling_price;
            $newProfitPercent = 0;
            if ($totalCost > 0) {
                $newProfitPercent = (($sellingPrice - $totalCost) / $totalCost) * 100;
            }

            $this->baseUnit->update([
                'purchase_price' => $totalCost,
                'cost_price'     => $totalCost,
                'profit_percent' => $newProfitPercent,
            ]);
        }
        
        // Update product table shortcut field
        $this->update(['base_cost_price' => $totalCost]);
        
        return $totalCost;
    }

    /**
     * 🔥 تحديث تكاليف جميع الوجبات والمكونات المركبة
     */
    public static function recalculateAllMeals()
    {
        $products = self::whereIn('product_type', ['meal', 'compound'])->get();
        foreach ($products as $product) {
            $product->recalculateMealCost();
        }
        return $products->count();
    }

    // =========================================================
    // 🔥 منطقة الصور (MediaLibrary Implementation) 🔥
    // =========================================================

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
              ->width(200)
              ->height(200)
              ->sharpen(10);
    }

    public function getImageUrlAttribute()
    {
        $url = $this->getFirstMediaUrl('products', 'thumb');
        
        if (!$url) {
            return route('serve.media.workaround', ['path' => 'images/default-product.png']);
        }
        
        // نبحث عن كلمة storage/ لقص ما بعدها وضمان التحويل للمسار البديل
        $search = 'storage/';
        $pos = strpos($url, $search);
        
        if ($pos !== false) {
            $path = substr($url, $pos + strlen($search));
            // نتأكد من فك تشفير الرابط (مثل الحروف العربية والمسافات) قبل تمريره للراوت
            $path = urldecode($path);
            // حذف أي متغيرات استعلام
            $path = explode('?', $path)[0];
            
            return route('serve.media.workaround', ['path' => ltrim($path, '/')]);
        }

        return $url;
    }
}