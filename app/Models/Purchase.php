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
        
        // 1. نعتبر القيمة القادمة من قاعدة البيانات هي UTC (توقيت جرينتش)
        // 2. ثم نحولها لتوقيت المتجر الحالي (الذي تم ضبطه في الإعدادات)
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC')
                    ->setTimezone(config('app.timezone'));
    }

    public function getCreatedAtAttribute($value)
    {
        if (!$value) return null;
        
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC')
                    ->setTimezone(config('app.timezone'));
    }
    
    public function getUpdatedAtAttribute($value)
    {
        if (!$value) return null;
        
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC')
                    ->setTimezone(config('app.timezone'));
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}