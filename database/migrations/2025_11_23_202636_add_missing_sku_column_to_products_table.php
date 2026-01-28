<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingSkuColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // 👈🏻 إضافة العمود: SKU كـ String، يسمح بـ NULL، ويكون فريداً
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
        Schema::table('products', function (Blueprint $table) {
            // 👈🏻 حذف الحقل عند التراجع
            $table->dropColumn('sku');
        });
    }
}