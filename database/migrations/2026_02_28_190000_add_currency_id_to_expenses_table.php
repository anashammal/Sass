<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyIdToExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            // exchange_rate: سعر الصرف مقابل العملة الأساسية وقت تسجيل المصروف
            $table->unsignedBigInteger('currency_id')->nullable()->after('amount');
            $table->decimal('exchange_rate', 15, 6)->nullable()->after('currency_id')->comment('Rate vs base currency at time of entry');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'exchange_rate']);
        });
    }
}
