<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductUnit extends Model implements HasMedia
{
    use InteractsWithMedia; // 👈 إضافة التريت

    protected $guarded = [];
    protected $appends = ['image'];

    protected $casts = [
        'is_base_unit' => 'boolean',
        'is_purchase' => 'boolean',
        'is_sale' => 'boolean',
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
              ->width(200)
              ->height(200)
              ->sharpen(10);
    }

    public function getImageAttribute()
    {
        $url = $this->getFirstMediaUrl('unit_images', 'thumb');
        
        if (!$url) {
            return route('serve.media.workaround', ['path' => 'images/default-product.png']);
        }
        
        // ✅ الحل الجذري: نستخدم RegEx لالتقاط أي مسار يأتي بعد /storage/ بغض النظر عن الدومين
        if (preg_match('/\/storage\/(.*)$/i', $url, $matches)) {
            $path = $matches[1];
            // 1. فك تشفير الرابط (لعلاج الأسماء العربية)
            $path = urldecode($path);
            // 2. حذف أي متغيرات (مثل ?v=...)
            $path = explode('?', $path)[0];
            
            // ✅ الحل الهجين الذكي (Smart Hybrid Fix)
            $generatedUrl = url('storage-files/' . $path);

            // استخراج مسار المجلد الفرعي من APP_URL
            $configUrl = config('app.url');
            $configPath = parse_url($configUrl, PHP_URL_PATH) ?? '';
            $configPath = rtrim($configPath, '/');

            // إذا كان المسار مفقوداً في الرابط المولد، نحقنه يدوياً
            if (!empty($configPath) && !str_contains($generatedUrl, $configPath)) {
                $schemeHost = request()->getSchemeAndHttpHost();
                if (str_starts_with($generatedUrl, $schemeHost)) {
                    return $schemeHost . $configPath . '/storage-files/' . $path;
                }
            }
            
            return $generatedUrl;
        }

        return $url;
    }
}