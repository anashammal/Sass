<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * خصم المخزون وحساب التكلفة الدقيقة بنظام FIFO
     * * @param Product $product المنتج المباع
     * @param float $quantitySold الكمية المباعة (بالوحدة الأساسية)
     * @return float التكلفة الإجمالية لهذه الكمية المباعة
     */
    public function reduceStock(Product $product, float $quantitySold): float
    {
        // 1. إذا كان المنتج لا يتتبع المخزون (مثل خدمة توصيل)
        if (!$product->track_stock) {
            return 0;
        }

        // 2. التحقق هل المنتج عبارة عن وجبة/وصفة (نظام المطاعم)
        // [المصدر: ملاحظات اضافية لتصميم النظام2.txt]
        $recipes = $product->recipes;
        if ($recipes->count() > 0) {
            return $this->handleRecipeDeduction($recipes, $quantitySold);
        }

        // 3. التعامل مع المنتجات العادية (نظام FIFO)
        // [المصدر: ملاحظات اضافية لتصميم النظام2.txt - حساب الربح FIFO]
        return $this->handleFifoDeduction($product, $quantitySold);
    }

    /**
     * معالجة خصم مكونات الوجبة
     */
    private function handleRecipeDeduction($recipes, float $quantitySold): float
    {
        $totalCost = 0;

        foreach ($recipes as $recipe) {
            // حساب الكمية المستهلكة من المكون: (كمية الوصفة × الكمية المباعة)
            // مثال: 100 جرام لحم × 2 سندويش = 200 جرام
            $ingredientNeededQty = $recipe->quantity * $quantitySold;

            // إضافة نسبة الهدر (Wastage) إن وجدت
            if ($recipe->wastage_percent > 0) {
                $ingredientNeededQty += ($ingredientNeededQty * ($recipe->wastage_percent / 100));
            }

            // استدعاء دالة الخصم بشكل تكراري للمكون (Recursive)
            // هذا يسمح بخصم المكونات حتى لو كانت المكونات نفسها مركبة
            $totalCost += $this->reduceStock($recipe->ingredient, $ingredientNeededQty);
        }

        return $totalCost;
    }

    /**
     * معالجة الخصم من الدفعات بنظام ما يدخل أولاً يخرج أولاً
     */
    private function handleFifoDeduction(Product $product, float $neededQty): float
    {
        $totalCost = 0;
        $remainingQty = $neededQty;

        // جلب الدفعات التي بها رصيد، مرتبة حسب الأقدم (تاريخ الصلاحية أولاً ثم تاريخ الإنشاء)
        $batches = $product->batches()
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc') // الأولوية لانتهاء الصلاحية
            ->orderBy('created_at', 'asc')  // ثم الأقدم شراءً
            ->get();

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            // تحديد الكمية التي سنأخذها من هذه الدفعة
            $qtyToTake = min($batch->quantity, $remainingQty);

            // حساب تكلفة هذه الكمية بناءً على سعر شراء هذه الدفعة تحديداً
            $totalCost += ($qtyToTake * $batch->cost_price);

            // خصم الكمية من الدفعة
            $batch->quantity -= $qtyToTake;
            $batch->save();

            // إنقاص الكمية المطلوبة
            $remainingQty -= $qtyToTake;
        }

        // تحديث المخزون الكلي للمنتج (Current Stock) للعرض السريع
        $product->decrement('current_stock', ($neededQty - $remainingQty));

        // إذا بقي كمية لم نجد لها رصيد (بيعة بالسالب/على المكشوف)
        if ($remainingQty > 0) {
            // هنا نسجل الكمية المتبقية بتكلفة "آخر سعر شراء" مسجل للمنتج
            $totalCost += ($remainingQty * $product->last_cost_price);
            
            // نجعل المخزون الكلي بالسالب
            $product->decrement('current_stock', $remainingQty);
        }

        return $totalCost;
    }
}