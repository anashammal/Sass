<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_units', function (Blueprint $table) {
            // إضافة عمود 'شراء' إذا لم يكن موجوداً
            if (!Schema::hasColumn('product_units', 'is_purchase')) {
                $table->boolean('is_purchase')->default(true)->after('barcode');
            }
            // إضافة عمود 'بيع' إذا لم يكن موجوداً
            if (!Schema::hasColumn('product_units', 'is_sale')) {
                $table->boolean('is_sale')->default(true)->after('is_purchase');
            }
        });
    }

    public function down()
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn(['is_purchase', 'is_sale']);
        });
    }
};