<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // هذا الأمر السحري يقوم بتحويل الجدول وكل الأعمدة النصية بداخله (مهما كانت أسمائها)
        // إلى الترميز الداعم للعربية والرموز
        DB::statement('ALTER TABLE contacts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down()
    {
        // لا حاجة للتراجع
    }
};