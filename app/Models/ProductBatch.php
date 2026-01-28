<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $guarded = [];
    protected $casts = ['expiry_date' => 'date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // دالة مساعدة لمعرفة هل الدفعة منتهية
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }
    
    // دالة مساعدة لمعرفة هل اقترب الانتهاء
    public function getIsNearExpiryAttribute()
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date <= now()->addDays($this->alert_days);
    }
}