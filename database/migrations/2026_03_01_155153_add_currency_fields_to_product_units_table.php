<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyFieldsToProductUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_price_currency_id')->nullable()->after('purchase_price');
            $table->unsignedBigInteger('sell_price_currency_id')->nullable()->after('selling_price');

            $table->foreign('purchase_price_currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->foreign('sell_price_currency_id')->references('id')->on('currencies')->onDelete('set null');
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
            $table->dropForeign(['purchase_price_currency_id']);
            $table->dropForeign(['sell_price_currency_id']);
            $table->dropColumn(['purchase_price_currency_id', 'sell_price_currency_id']);
        });
    }
}
