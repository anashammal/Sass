<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApplyContactBalanceColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            // إضافة عمود الرصيد الفعلي بعد حد الدين
            if (!Schema::hasColumn('contacts', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('credit_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
}
