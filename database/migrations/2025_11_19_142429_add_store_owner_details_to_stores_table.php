<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreOwnerDetailsToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // هذه هي الأعمدة التي يتوقعها الـ Controller بناءً على الكود الأخير:
            $table->string('owner_name')->after('owner_id')->nullable();
            $table->string('owner_email')->after('owner_name')->nullable();
            $table->string('database_name')->after('subdomain')->nullable();
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('owner_name');
            $table->dropColumn('owner_email');
            $table->dropColumn('database_name');
        });
    }
}