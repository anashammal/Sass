<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_store_owner')->default(false)->after('type');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_withdrawal')->default(false)->after('due');
            $table->bigInteger('withdrawal_number')->nullable()->after('is_withdrawal');
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('is_store_owner');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['is_withdrawal', 'withdrawal_number']);
        });
    }
};
