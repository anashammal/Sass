<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxRatesToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // إضافة عمود لتخزين نسب الضرائب، الافتراضي هو 0 و 15
            // نتأكد أولاً أن العمود غير موجود لتجنب الأخطاء
            if (!Schema::hasColumn('stores', 'tax_rates')) {
                $table->string('tax_rates')->default('0,15')->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'tax_rates')) {
                $table->dropColumn('tax_rates');
            }
        });
    }
}