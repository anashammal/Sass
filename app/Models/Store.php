<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'owner_id',
        'status',
        'country',
        'city',
        'phone_number',
        'iban',
        'email',
        // الحقول الجديدة
        'logo_path',
        'signature_path',
        'stamp_path',
        'tax_number',
        'default_tax_percentage',
        'country_code',
        'invoice_mode',
        'timezone',
        'clock_type',
        'clock_theme',
        'notify_email',
        'notify_whatsapp',
        'daily_report_time',
        'type',
    ];

    // علاقة مع صاحب المتجر (User)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // --- الدوال المساعدة (Accessors) ---

    // 1. دالة لجلب رابط الشعار (النظام الجديد)
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        
        // جلب شعار النظام الافتراضي
        $systemLogo = \App\Models\SystemSetting::where('key', 'system_default_logo')->value('value');
        return $systemLogo ? asset('storage/' . $systemLogo) : asset('images/default-logo.png');
    }

    // 2. دالة التوافق (Compatibility Fix) - هذه الدالة ستحل مشكلة الخطأ
    // عندما يطلب ملف العرض القديم هذه الدالة، سنعطيه الرابط الجديد
    public function getFirstMediaUrl($collectionName = 'default', $conversionName = '')
    {
        return $this->logo_url; // استدعاء الدالة رقم 1
    }
public function getIsWhatsappLinkedAttribute()
{
    // افترضنا أنك تخزن حالة الاتصال أو التوكن في جدول settings أو عمود بالمتجر
    // عدلها حسب طريقة ربطك، هذا مثال شائع:
    return !empty($this->whatsapp_token) || !empty($this->whatsapp_session);
}
}