<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('product_batches', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');
        $table->decimal('quantity', 10, 2); // الكمية المتبقية من هذه الدفعة
        $table->decimal('cost_price', 10, 2); // سعر التكلفة لهذه الدفعة (لحل مشكلة الربح)
        $table->date('expiry_date')->nullable(); // تاريخ الانتهاء
        $table->integer('alert_days')->default(30); // التنبيه قبل كم يوم؟
        $table->timestamps();

        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_batches');
    }
}
