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
        if ($this->product_type !== 'meal') return;

        $totalCost = 0;
        $this->load('recipes.ingredient.units');

        foreach ($this->recipes as $recipe) {
            $ingredient = $recipe->ingredient;
            if ($ingredient && $ingredient->baseUnit) {
                // نستخدم تكلفة الوحدة الأساسية للمكون (خامة)
                $totalCost += ($recipe->quantity * $ingredient->baseUnit->cost_price);
            }
        }

        // تحديث سعر التكلفة والربح للوحدة الأساسية لهذه الوجبة
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
            
            // تحديث تكلفة المنتج نفسه (إذا كان مخزناً في حقل مستقل)
            $this->update(['base_cost_price' => $totalCost]);
        }
        
        return $totalCost;
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
        $url = $this->getFirstMediaUrl('products', 'thumb') ?: asset('images/default-product.png');
        
        // Extract relative path starting from storage/ or images/
        if (strpos($url, '/storage/') !== false) {
            $path = explode('/storage/', $url, 2)[1];
            return rtrim(request()->getBaseUrl(), '/') . '/storage/' . $path;
        } elseif (strpos($url, '/images/') !== false) {
             $path = explode('/images/', $url, 2)[1];
             return rtrim(request()->getBaseUrl(), '/') . '/images/' . $path;
        }

        return parse_url($url, PHP_URL_PATH);
    }
}