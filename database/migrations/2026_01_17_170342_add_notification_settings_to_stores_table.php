<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationSettingsToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // إضافة الأعمدة فقط إذا لم تكن موجودة
            if (!Schema::hasColumn('stores', 'notify_email')) {
                $table->boolean('notify_email')->default(true)->after('tax_number'); // الافتراضي مفعل
            }
            if (!Schema::hasColumn('stores', 'notify_whatsapp')) {
                $table->boolean('notify_whatsapp')->default(false)->after('notify_email'); // الافتراضي غير مفعل
            }
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['notify_email', 'notify_whatsapp']);
        });
    }
}