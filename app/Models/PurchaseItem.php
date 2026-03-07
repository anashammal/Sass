<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    // 🟢 هنا التعديل: حددنا الحقول المسموح بحفظها بدقة (بما فيها الجديدة)
    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_unit_id',
        'quantity',
        'unit_price',
        'total_cost',
        'quantity_in_base_unit',
        'cost_per_base_unit',
        'expiry_date', // ✅ حقل تاريخ الانتهاء
        'alert_days',   // ✅ حقل أيام التنبيه
        'selling_price',
        'discount',
        'discount_type',
        'tax_percent'
    ];

    // الفاتورة التابعة لها
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // المنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // الوحدة المستخدمة
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}