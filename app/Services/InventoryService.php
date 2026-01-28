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
        // لا يزال بإمكاننا تقدير التكلفة بناءً على "آخر سعر شراء" مسجل
        if (!$product->track_stock) {
            return (float)($quantitySold * ($product->last_cost_price ?? 0));
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
            // محاولة جلب تكلفة من آخر دفعة مسجلة حتى لو كميتها صفر (كأفضل تقدير)
            $fallbackCost = $product->last_cost_price;
            if (!$fallbackCost || $fallbackCost == 0) {
                $lastBatch = $product->batches()->latest()->first();
                if ($lastBatch) $fallbackCost = $lastBatch->cost_price;
            }
            
            // ✅ إضافة: فحص سعر التكلفة من الوحدات كحل أخير (مهم للخبز والسلع المباشرة)
            if (!$fallbackCost || $fallbackCost == 0) {
                $baseUnit = $product->units()->where('is_base_unit', true)->first();
                if (!$baseUnit) $baseUnit = $product->units()->first();
                if ($baseUnit) $fallbackCost = $baseUnit->cost_price;
            }

            $totalCost += ($remainingQty * ($fallbackCost ?? 0));
            
            // نجعل المخزون الكلي بالسالب
            $product->decrement('current_stock', $remainingQty);
        }

        return $totalCost;
    }

    /**
     * إرجاع المخزون (عند حذف فاتورة أو إرجاع صنف)
     * سيتم إعادة الكمية لآخر دفعة مسجلة للمنتج
     */
    public function incrementStock(Product $product, float $quantityToReturn): void
    {
        if (!$product->track_stock) return;

        // 1. إذا كان المنتج عبارة عن وجبة، نرجع المكونات
        $recipes = $product->recipes;
        if ($recipes->count() > 0) {
            foreach ($recipes as $recipe) {
                $ingredientQty = $recipe->quantity * $quantityToReturn;
                if ($recipe->wastage_percent > 0) {
                    $ingredientQty += ($ingredientQty * ($recipe->wastage_percent / 100));
                }
                $this->incrementStock($recipe->ingredient, $ingredientQty);
            }
            return;
        }

        // 2. محاولة إرجاع الكمية لآخر دفعة نشطة (أو آخر دفعة تم إنشاؤها)
        $batch = $product->batches()->latest()->first();
        
        if ($batch) {
            $batch->increment('quantity', $quantityToReturn);
        } else {
            // إذا لم يكن هناك دفعات (حالة نادرة)، ننشئ دفعة افتراضية لإرجاع المخزون إليها
            ProductBatch::create([
                'product_id' => $product->id,
                'quantity' => $quantityToReturn,
                'cost_price' => $product->last_cost_price ?? 0,
                'expiry_date' => $product->expiry_date,
            ]);
        }

        // 3. تحديث المخزون الكلي للمنتج
        $product->increment('current_stock', $quantityToReturn);
    }
}