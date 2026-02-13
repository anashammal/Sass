<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInventoryActionLogsTableAddQuantities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_action_logs', function (Blueprint $table) {
            $table->decimal('old_quantity', 15, 3)->nullable()->after('quantity');
            $table->decimal('new_quantity', 15, 3)->nullable()->after('old_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_action_logs', function (Blueprint $table) {
            $table->dropColumn(['old_quantity', 'new_quantity']);
        });
    }
}
