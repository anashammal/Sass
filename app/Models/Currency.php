<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'name_tr',
        'code',
        'symbol',
        'is_active',
    ];

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_currencies')
                    ->withPivot('custom_rate')
                    ->withTimestamps();
    }
}
