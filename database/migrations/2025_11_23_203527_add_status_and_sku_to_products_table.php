<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndSkuToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('products', function (Blueprint $table) {
        // 👈🏻 ترك فقط عمود status (العمود الذي نحتاجه فعلاً)
        $table->string('status')->default('active')->after('category_id');
        
        // ❌ حذف هذا السطر: $table->string('sku')->nullable()->unique()->after('name_en');
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
        // 👈🏻 حذف كلا العمودين: status و sku
        $table->dropColumn(['status', 'sku']); 
    });
}
}