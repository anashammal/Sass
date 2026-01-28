<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlertDaysToPurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('purchase_items', function (Blueprint $table) {
        $table->integer('alert_days')->default(10)->after('expiry_date');
    });
}

public function down()
{
    Schema::table('purchase_items', function (Blueprint $table) {
        $table->dropColumn('alert_days');
    });
}
}
