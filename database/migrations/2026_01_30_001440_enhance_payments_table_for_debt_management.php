<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhancePaymentsTableForDebtManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->after('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->after('sale_id')->constrained()->onDelete('cascade');
            $table->date('payment_date')->nullable()->after('amount');
            $table->text('notes')->nullable()->after('payment_date');
            $table->string('attachment')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
}
