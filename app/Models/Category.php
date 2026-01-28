<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بملئها (للحماية).
     */
    protected $fillable = [
        'store_id',
        'name',
        'parent_id',
    ];

    /**
     * !! -- هذا هو الكود الجديد -- !!
     * تعريف العلاقة: هذا التصنيف "ينتمي إلى" أب واحد.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * !! -- وهذا كود إضافي سنحتاجه لاحقاً -- !!
     * تعريف العلاقة: هذا التصنيف "يمتلك" عدة أبناء.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * تعريف العلاقة: هذا التصنيف "ينتمي إلى" متجر واحد.
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
