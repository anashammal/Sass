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
        
        // نبحث عن كلمة storage/ لقص ما بعدها وضمان التحويل للمسار البديل
        $search = 'storage/';
        $pos = strpos($url, $search);
        
        if ($pos !== false) {
            $path = substr($url, $pos + strlen($search));
            // نتأكد من فك تشفير الرابط (مثل الحروف العربية والمسافات) قبل تمريره للراوت
            $path = urldecode($path);
            // حذف أي متغيرات استعلام
            $path = explode('?', $path)[0];
            
            return route('serve.media.workaround', ['path' => ltrim($path, '/')]);
        }

        return $url;
    }
}