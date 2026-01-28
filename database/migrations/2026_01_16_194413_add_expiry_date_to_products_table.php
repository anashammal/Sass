<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // نضيف عمود تاريخ الصلاحية (يقبل قيمة فارغة للمنتجات التي ليس لها صلاحية)
            $table->date('expiry_date')->nullable()->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });
    }
};