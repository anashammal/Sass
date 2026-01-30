<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnsTable extends Migration
{
    public function up()
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('sale_item_id')->nullable(); // قد يُحذف الـ item بعد الإرجاع
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('quantity', 10, 2); // الكمية المرتجعة
            $table->decimal('price', 10, 2); // سعر الوحدة وقت الإرجاع
            $table->decimal('total', 10, 2); // الإجمالي المرتجع
            $table->unsignedBigInteger('user_id')->nullable(); // من قام بالإرجاع
            $table->text('reason')->nullable(); // سبب الإرجاع
            $table->timestamps();
            
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
        
        // إضافة عمود لتتبع إجمالي المرتجعات في جدول المبيعات
        if (!Schema::hasColumn('sales', 'total_returns')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('total_returns', 10, 2)->default(0)->after('total');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sale_returns');
        
        if (Schema::hasColumn('sales', 'total_returns')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('total_returns');
            });
        }
    }
}
