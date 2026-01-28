<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. جدول المنتجات الرئيسي
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('slug')->nullable(); 
            $table->text('description')->nullable();
            
            // إعدادات
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->decimal('alert_quantity', 10, 2)->default(5);
            $table->decimal('tax_percent', 5, 2)->default(0);

            $table->timestamps();
        });

        // 2. جدول وحدات المنتج (السر هنا)
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->string('unit_name'); // قطعة، كرتون، طبق
            
            // معامل التحويل (القطعة = 1، الكرتون = 24)
            $table->decimal('conversion_factor', 12, 4); 
            
            // دقة عالية جداً (20 رقم، 8 بعد الفاصلة) لمنع أخطاء التقريب
            $table->decimal('cost_price', 20, 8)->default(0); 
            $table->decimal('selling_price', 20, 8)->default(0); 
            
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            
            // نوع الوحدة
            $table->boolean('is_base_unit')->default(false); // هل هي أصغر وحدة؟
            $table->boolean('is_purchase_unit')->default(false); // هل نشتري بها؟
            $table->boolean('is_sale_unit')->default(true); // هل نبيع بها؟

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
    }
};