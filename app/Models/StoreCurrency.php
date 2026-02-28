<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StoreCurrency extends Pivot
{
    use HasFactory;

    protected $table = 'store_currencies';

    protected $fillable = [
        'store_id',
        'currency_id',
        'custom_rate',
    ];
}
