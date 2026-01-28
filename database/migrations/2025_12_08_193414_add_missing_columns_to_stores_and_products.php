<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. حل مشكلة التوقيع والختم للمتاجر
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('stores', 'stamp_path')) {
                $table->string('stamp_path')->nullable()->after('signature_path');
            }
        });

        // 2. إضافة حالة التفعيل للمنتجات
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('tax_percent');
            }
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'stamp_path']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
};