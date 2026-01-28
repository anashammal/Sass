<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddBrandingAndTaxToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. فحص ذكي: هل الأعمدة موجودة مسبقاً؟
        // إذا لم يكن عمود 'logo_path' موجوداً، سنقوم بإضافة الأعمدة.
        if (!Schema::hasColumn('stores', 'logo_path')) {
            Schema::table('stores', function (Blueprint $table) {
                // الهوية البصرية
                $table->string('logo_path')->nullable()->comment('مسار شعار المتجر');
                $table->string('signature_path')->nullable()->comment('مسار التوقيع الشفاف');
                $table->string('stamp_path')->nullable()->comment('مسار الختم الرسمي');

                // الإعدادات الضريبة والامتثال
                $table->string('tax_number')->nullable()->comment('الرقم الضريبي VAT ID');
                $table->decimal('default_tax_percentage', 5, 2)->default(15.00)->comment('نسبة الضريبة الافتراضية');
                $table->string('country_code')->default('SA')->comment('كود الدولة: SA, TR, etc');
                $table->enum('invoice_mode', ['zatca_phase_1', 'turkey_kdv', 'simple'])->default('simple')->comment('نوع نظام الفوترة');
            });
        }

        // 2. إنشاء جدول إعدادات النظام العامة (للسوبر أدمن)
        // فحص ذكي: هل الجدول موجود؟
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); 
                $table->text('value')->nullable(); 
                $table->timestamps();
            });

            // إضافة البيانات الافتراضية
            DB::table('system_settings')->insert([
                ['key' => 'system_default_logo', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'system_currency', 'value' => 'SAR', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_settings');
        
        if (Schema::hasColumn('stores', 'logo_path')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn([
                    'logo_path', 
                    'signature_path', 
                    'stamp_path', 
                    'tax_number', 
                    'default_tax_percentage',
                    'country_code',
                    'invoice_mode'
                ]);
            });
        }
    }
}