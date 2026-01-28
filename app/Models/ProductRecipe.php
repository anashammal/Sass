<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_product_id',
        'ingredient_product_id',
        'unit_id',
        'quantity',
        'wastage_percent'
    ];

    // العلاقة مع المنتج الأصلي (الوجبة)
    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    // العلاقة مع المكون (الخام)
    public function ingredient()
    {
        return $this->belongsTo(Product::class, 'ingredient_product_id');
    }

    // العلاقة مع الوحدة
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }
}