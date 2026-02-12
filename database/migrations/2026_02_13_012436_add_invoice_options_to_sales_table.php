<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceOptionsToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('show_iban')->default(false)->after('status');
            $table->boolean('show_stamp')->default(false)->after('show_iban');
            $table->boolean('show_signature')->default(false)->after('show_stamp');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['show_iban', 'show_stamp', 'show_signature']);
        });
    }
}
