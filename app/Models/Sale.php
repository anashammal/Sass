<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $guarded = [];

    // علاقة الفاتورة بالمتجر
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // علاقة الفاتورة بالعميل
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // علاقة الفاتورة بالمستخدم (الكاشير)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة الفاتورة بالأصناف
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
public function payments()
{
    return $this->hasMany(Payment::class);
}
}