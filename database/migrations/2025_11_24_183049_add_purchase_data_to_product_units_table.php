<?php
// AddPurchaseDataToProductUnitsTable.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseDataToProductUnitsTable extends Migration
{
    public function up()
    {
        Schema::table('product_units', function (Blueprint $table) {
            // سعر شراء العبوة الكبيرة
            $table->decimal('purchase_price', 10, 2)->nullable()->after('cost_price');
            // عدد القطع داخل العبوة
            $table->integer('bulk_quantity')->nullable()->after('purchase_price');
        });
    }

    public function down()
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'bulk_quantity']);
        });
    }
}