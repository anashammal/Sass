<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddTaxAndAddressToContactsTable extends Migration
{
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('tax_number')->nullable()->after('email');
            $table->string('address')->nullable()->after('tax_number');
        });
    }
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['tax_number', 'address']);
        });
    }
}
