<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // ??? ????? ??? ???? ?????? ???? ???????? ??????? ?? ??????
    protected $guarded = []; 

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }
}