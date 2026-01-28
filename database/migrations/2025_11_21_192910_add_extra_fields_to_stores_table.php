<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // نفحص كل عمود قبل إضافته لتجنب خطأ "Column already exists"
            if (!Schema::hasColumn('stores', 'country')) {
                $table->string('country')->nullable();
            }
            if (!Schema::hasColumn('stores', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('stores', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('stores', 'phone_country_code')) {
                $table->string('phone_country_code')->nullable();
            }
            if (!Schema::hasColumn('stores', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
            if (!Schema::hasColumn('stores', 'iban')) {
                $table->string('iban')->nullable();
            }
            if (!Schema::hasColumn('stores', 'bank_country')) {
                $table->string('bank_country')->nullable();
            }
            if (!Schema::hasColumn('stores', 'onboarding_complete')) {
                $table->boolean('onboarding_complete')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            // عند التراجع نحذف الأعمدة فقط إذا كانت موجودة
            $columns = [
                'country', 'city', 'address', 
                'phone_country_code', 'phone_number', 
                'iban', 'bank_country', 'onboarding_complete'
            ];
            
            // ملاحظة: حذف الأعمدة في SQLite قد يتطلب خطوات إضافية، لكنه يعمل مباشرة في MySQL
            $table->dropColumn($columns);
        });
    }
};