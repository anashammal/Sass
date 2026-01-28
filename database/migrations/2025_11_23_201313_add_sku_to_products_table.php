<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingSkuToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 👈🏻 إضافة الحقل الفريد المفقود
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 👈🏻 حذف الحقل عند التراجع
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
}