<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHistoricalFieldsToPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('selling_price', 15, 4)->nullable()->after('unit_price');
            $table->decimal('discount', 10, 2)->default(0)->after('selling_price');
            $table->string('discount_type')->default('fixed')->after('discount');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['selling_price', 'discount', 'discount_type', 'tax_percent']);
        });
    }
}
