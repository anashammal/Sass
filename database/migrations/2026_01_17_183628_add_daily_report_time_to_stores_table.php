<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyReportTimeToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('stores', function (Blueprint $table) {
        if (!Schema::hasColumn('stores', 'daily_report_time')) {
            // الوقت الذي يختاره المستخدم للتقرير اليومي
            $table->time('daily_report_time')->nullable()->after('notify_whatsapp');
        }
    });
}

public function down()
{
    Schema::table('stores', function (Blueprint $table) {
        $table->dropColumn('daily_report_time');
    });
}
}
