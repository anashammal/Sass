<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonColumnToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // إضافة عمود "السبب" (يمكن أن يكون فارغاً) بعد عمود "الحالة"
            $table->text('status_reason')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('status_reason');
        });
    }
}
