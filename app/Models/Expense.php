<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * المبلغ محوّل للعملة الأساسية باستخدام سعر الصرف المسجّل
     */
    public function getAmountInBaseCurrencyAttribute()
    {
        if ($this->exchange_rate && $this->exchange_rate > 0) {
            return $this->amount * $this->exchange_rate;
        }
        return $this->amount;
    }
}
