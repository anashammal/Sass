<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfitPercentToProductUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_units', function (Blueprint $table) {
            // 👈🏻 إضافة عمود نسبة الربح بدقة عالية (20 رقماً، 10 منها بعد الفاصلة)
            $table->decimal('profit_percent', 20, 10)->nullable()->default(0)->after('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn('profit_percent');
        });
    }
}