<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            
            // 1. إضافة عمود المخزون بعد alert_quantity (لأنه موجود بالتأكيد)
            if (!Schema::hasColumn('products', 'current_stock')) {
                $table->decimal('current_stock', 10, 2)->default(0)->after('alert_quantity');
            }
            
            // 2. إضافة التكلفة بعد المخزون الجديد (بدلاً من purchase_price غير الموجود)
            if (!Schema::hasColumn('products', 'last_cost_price')) {
                // التغيير هنا: وضعناه بعد current_stock
                $table->decimal('last_cost_price', 10, 4)->default(0)->after('current_stock');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['current_stock', 'last_cost_price']);
        });
    }
};