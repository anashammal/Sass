<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $guarded = [];
// علاقة عكسية للوصول للفاتورة الأم (ضروري للإرجاع)
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // علاقة للوصول لاسم الوحدة (كرتون/حبة) في التقارير
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}