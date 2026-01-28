<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductRecipesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->id();

            // المنتج الرئيسي (الوجبة/السندويش) الذي سيتم بيعه
            $table->foreignId('parent_product_id')->constrained('products')->onDelete('cascade');

            // المكون الخام (لحم، طماطم) الذي سيتم خصمه من المخزون
            $table->foreignId('ingredient_product_id')->constrained('products')->onDelete('cascade');

            // الوحدة المستخدمة في الوصفة (مثلاً جرام) ويجب أن تكون من وحدات المكون الخام
            $table->foreignId('unit_id')->nullable()->constrained('product_units')->onDelete('set null');

            // الكمية المستهلكة من المكون لهذه الوجبة
            $table->decimal('quantity', 10, 4);

            // نسبة الفاقد (الهدر) أثناء التحضير - اختياري
            $table->decimal('wastage_percent', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_recipes');
    }
}