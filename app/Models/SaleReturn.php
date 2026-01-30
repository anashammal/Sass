<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // العلاقة مع الفاتورة
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // العلاقة مع المنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // العلاقة مع الوحدة
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    // المستخدم الذي قام بالإرجاع
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
