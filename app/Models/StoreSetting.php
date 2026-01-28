<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'taxes' => 'array', // تحويل تلقائي من JSON إلى Array والعكس
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}