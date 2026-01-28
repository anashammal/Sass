<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClockSettingsToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('stores', function (Blueprint $table) {
        $table->string('clock_type')->default('digital'); // digital, analog
        $table->string('clock_theme')->default('default'); // default, neon, modern, etc.
    });
}

public function down()
{
    Schema::table('stores', function (Blueprint $table) {
        $table->dropColumn(['clock_type', 'clock_theme']);
    });
}
}
