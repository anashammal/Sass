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
        'address',
        'latitude',
        'longitude',
    ];

    // علاقة مع صاحب المتجر (User)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // --- الدوال المساعدة (Accessors) ---

    // 1. دالة لجلب رابط الشعار (النظام الموحد)
    public function getLogoUrlAttribute()
    {
        if (!$this->logo_path) {
            // جلب شعار النظام الافتراضي
            $systemLogo = \App\Models\SystemSetting::where('key', 'system_default_logo')->value('value');
            if ($systemLogo) {
                return route('serve.media.workaround', ['path' => $systemLogo]);
            }
            return asset('images/default-logo.png');
        }

        return route('serve.media.workaround', ['path' => $this->logo_path]);
    }

    // 2. دالة لجلب رابط الختم
    public function getStampUrlAttribute()
    {
        if (!$this->stamp_path) return null;
        return route('serve.media.workaround', ['path' => $this->stamp_path]);
    }

    // 3. دالة لجلب رابط التوقيع
    public function getSignatureUrlAttribute()
    {
        if (!$this->signature_path) return null;
        return route('serve.media.workaround', ['path' => $this->signature_path]);
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