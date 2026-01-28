<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupAndTypeToSystemSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('system_settings', function (Blueprint $table) {
        // نضيف الأعمدة فقط إذا لم تكن موجودة
        if (!Schema::hasColumn('system_settings', 'group')) {
            $table->string('group')->default('general')->after('value')->index();
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
