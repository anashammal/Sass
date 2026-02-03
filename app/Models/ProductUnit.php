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

    // لتسهيل استدعاء الصورة لاحقاً
    public function getImageAttribute()
    {
        $url = $this->getFirstMediaUrl('unit_images', 'thumb') ?: asset('images/default-product.png');
        
        // Extract relative path
        if (strpos($url, '/storage/') !== false) {
            $path = explode('/storage/', $url, 2)[1];
            return rtrim(request()->getBaseUrl(), '/') . '/storage/' . $path;
        } elseif (strpos($url, '/images/') !== false) {
             $path = explode('/images/', $url, 2)[1];
             return rtrim(request()->getBaseUrl(), '/') . '/images/' . $path;
        }

        return parse_url($url, PHP_URL_PATH);
    }
}