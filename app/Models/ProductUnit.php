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
        $url = $this->getFirstMediaUrl('unit_images', 'thumb') ?: asset('images/default-product.png');
        
        // إذا كان الرابط الرامي للميديا يحتوي على storage/، سنقوم بتحويله فوراً للمسار البديل
        if (strpos($url, 'storage/') !== false) {
            $path = explode('storage/', $url, 2)[1];
            $path = explode('?', $path)[0]; // حذف أي متغيرات استعلام
            return route('serve.media.workaround', ['path' => $path]);
        }

        return $url;
    }
}