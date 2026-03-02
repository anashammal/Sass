<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExchangeRatesToProductUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->decimal('purchase_exchange_rate', 15, 4)->nullable()->after('purchase_price_currency_id');
            $table->decimal('sell_exchange_rate', 15, 4)->nullable()->after('sell_price_currency_id');
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
            $table->dropColumn(['purchase_exchange_rate', 'sell_exchange_rate']);
        });
    }
}
