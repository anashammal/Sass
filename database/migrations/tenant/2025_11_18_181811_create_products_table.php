<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المنتج
            $table->string('barcode')->nullable()->index(); // الباركود (مفهرس للسرعة)
            $table->string('unit')->default('piece'); // الوحدة (قطعة/كيلو)
            
            // الأسعار (نستخدم decimal للدقة المالية)
            $table->decimal('purchase_price', 10, 2)->default(0); // سعر الشراء
            $table->decimal('sale_price', 10, 2)->default(0); // سعر البيع
            
            $table->integer('stock')->default(0); // الكمية المتوفرة
            
            // سنضيف لاحقاً: التصنيف، العلامة التجارية، الصور، إلخ.
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}