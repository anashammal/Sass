<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToContactsTable extends Migration
{
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            // 👈🏻 التصحيح: نضع العمود بعد contact_name بدلاً من name
            if (!Schema::hasColumn('contacts', 'type')) {
                // نتحقق أولاً إذا كان العمود contact_name موجوداً
                if (Schema::hasColumn('contacts', 'contact_name')) {
                    $table->string('type')->default('customer')->after('contact_name');
                } else {
                    // إذا لم يوجد، نضيفه في النهاية
                    $table->string('type')->default('customer');
                }
            }
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}