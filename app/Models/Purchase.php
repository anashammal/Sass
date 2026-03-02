<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    // العلاقات
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Contact::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * الإجمالي محوّل للعملة الأساسية باستخدام سعر الصرف المسجّل
     */
    public function getGrandTotalInBaseCurrencyAttribute()
    {
        if ($this->exchange_rate && $this->exchange_rate > 0) {
            return $this->grand_total * $this->exchange_rate;
        }
        return $this->grand_total;
    }

    /**
     * المتبقي (غير المدفوع) محوّل للعملة الأساسية
     */
    public function getRemainingAmountInBaseCurrencyAttribute()
    {
        return $this->grand_total_in_base_currency - $this->paid_amount;
    }

    // =========================================================
    // 🔥 دوال تحويل التوقيت (Accessors) - النسخة المصححة 🔥
    // =========================================================

    /**
     * دالة خاصة لتحويل التاريخ من UTC (قاعدة البيانات) إلى توقيت المتجر
     */
    protected function serializeDateToStoreTime($value)
    {
        if (!$value) return null;

        // 1. نحدد أن القيمة الأصلية هي UTC (توقيت السيرفر العالمي)
        // 2. ثم نحولها إلى توقيت التطبيق الحالي (الذي تم ضبطه في الميدلويير حسب المتجر)
        return Carbon::parse($value, 'UTC')->timezone(config('app.timezone'));
    }

    // =========================================================
    // 🔥 دوال تحويل التوقيت (Accessors) - النسخة النهائية 🔥
    // =========================================================

    public function getInvoiceDateAttribute($value)
    {
        if (!$value) return null;
        
        try {
            return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCreatedAtAttribute($value)
    {
        if (!$value) return null;
        try {
            return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function getUpdatedAtAttribute($value)
    {
        if (!$value) return null;
        try {
            return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}