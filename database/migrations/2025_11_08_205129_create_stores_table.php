<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id(); // رقم المتجر
            
            // ربط المتجر بصاحبه (من جدول users)
            $table->unsignedBigInteger('owner_id'); 
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('name'); // اسم المتجر
            $table->string('subdomain')->unique(); // الدومين الفرعي
            
            // 🟢 هام جداً: اسم قاعدة البيانات الخاصة بالمتجر
            $table->string('database_name')->nullable(); 

            // 🟢 البيانات الاختيارية (nullable) ليتم تعبئتها لاحقاً من قبل صاحب المتجر
            $table->string('country')->nullable(); 
            $table->string('city')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('iban')->nullable(); 
            $table->string('iban_bank_name')->nullable(); 
            
            $table->string('logo_path')->nullable(); 
            $table->string('stamp_path')->nullable(); 
            
            // حالة المتجر
            $table->string('status')->default('active'); 
            // 🟢 سبب الإيقاف (في حال تم إيقاف المتجر مستقبلاً)
            $table->text('status_reason')->nullable(); 
            
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}