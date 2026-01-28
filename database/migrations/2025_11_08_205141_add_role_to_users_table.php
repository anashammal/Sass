<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // سنضيف عمود الصلاحية بعد عمود 'email'
            // سيكون 'superadmin' (أنت) أو 'store_owner' (صاحب المتجر)
            $table->string('role')->default('store_owner')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // هذا الكود للتراجع عن الإضافة (في حال احتجنا لحذف العمود)
            $table->dropColumn('role');
        });
    }
}