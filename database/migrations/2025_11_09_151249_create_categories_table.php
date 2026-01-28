<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    // !! هذا هو السطر الذي قمت بتصحيحه !!
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // رقم التصنيف

            // 1. لربط التصنيف بالمتجر
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            // 2. اسم التصنيف
            $table->string('name');

            // 3. لعمل التصنيفات الفرعية
            $table->unsignedBigInteger('parent_id')->nullable(); // nullable = يمكن أن يكون فارغًا
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');

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
        Schema::dropIfExists('categories');
    }
}
