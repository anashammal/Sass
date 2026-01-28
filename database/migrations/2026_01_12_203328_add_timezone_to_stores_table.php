<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimezoneToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('stores', function (Blueprint $table) {
        // القيمة الافتراضية الرياض
        $table->string('timezone')->default('Asia/Riyadh')->after('name'); 
    });
}

public function down()
{
    Schema::table('stores', function (Blueprint $table) {
        $table->dropColumn('timezone');
    });
}
}
