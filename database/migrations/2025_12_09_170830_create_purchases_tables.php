<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. جدول الفواتير الرئيسية (Purchases)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            // المورد (مرتبط بجدول جهات الاتصال contacts)
            $table->foreignId('supplier_id')->nullable()->constrained('contacts')->onDelete('set null');
            
            $table->string('invoice_number')->nullable(); // رقم فاتورة المورد الورقية
            $table->date('invoice_date');
            
            $table->decimal('sub_total', 10, 2)->default(0); // المجموع قبل الضريبة
            $table->decimal('tax_amount', 10, 2)->default(0); // قيمة الضريبة
            $table->decimal('discount_amount', 10, 2)->default(0); // خصم إضافي
            $table->decimal('grand_total', 10, 2)->default(0); // الصافي النهائي
            
            $table->decimal('paid_amount', 10, 2)->default(0); // المدفوع
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'card', 'bank', 'deferred'])->default('cash'); // طريقة الدفع
            
            $table->text('notes')->nullable();
            // صورة الفاتورة (مرفق) [مرجع: تصميم النظام بند 56]
            $table->string('attachment')->nullable(); 
            
            $table->timestamps();
        });

        // 2. جدول تفاصيل الفاتورة (Purchase Items) - وهو أيضاً سجل حركة الأسعار
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // الوحدة التي تم الشراء بها (مثلاً: كرتون)
            $table->foreignId('product_unit_id')->nullable()->constrained()->onDelete('set null');
            
            $table->decimal('quantity', 10, 2); // الكمية المشتراة (مثلاً 5 كرتون)
            $table->decimal('unit_price', 10, 2); // سعر شراء الوحدة (مثلاً 50 ريال للكرتون)
            
            // حقول مهمة جداً للحسابات الدقيقة [مرجع: تصميم النظام بند 28]
            $table->decimal('quantity_in_base_unit', 10, 2); // الكمية محولة للوحدة الأساسية (مثلاً 150 بيضة)
            $table->decimal('cost_per_base_unit', 10, 4); // تكلفة القطعة الواحدة بدقة عالية (مثلاً 0.3333 ريال)
            
            $table->decimal('total_cost', 10, 2); // الإجمالي للسطر
            
            $table->date('expiry_date')->nullable(); // تاريخ الانتهاء [مرجع: تصميم النظام بند 53]
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};