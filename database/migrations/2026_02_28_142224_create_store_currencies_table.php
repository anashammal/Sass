<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('store_currencies')) {
            Schema::create('store_currencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->onDelete('cascade');
                $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
                $table->decimal('custom_rate', 15, 6)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_currencies');
    }
}
