<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostToSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // إضافة عمود التكلفة لتثبيت تكلفة المنتج وقت البيع
            // هذا ضروري لحساب الربح بدقة (FIFO) حتى لو تغير سعر الشراء لاحقاً
            $table->decimal('cost', 12, 4)->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
}