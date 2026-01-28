<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('system_settings', function (Blueprint $table) {
        if (!Schema::hasColumn('system_settings', 'group')) {
            $table->string('group')->default('general')->after('value');
        }
        if (!Schema::hasColumn('system_settings', 'type')) {
            $table->string('type')->default('text')->after('group');
        }
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_settings', function (Blueprint $table) {
            //
        });
    }
}
