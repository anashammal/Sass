<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApplyNewContactFields extends Migration
{
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            // !! -- إضافة العمود tax_number فقط -- !!
            // (عمود address موجود بالفعل في ملف create_contacts_table)
            if (!Schema::hasColumn('contacts', 'tax_number')) {
                $table->string('tax_number')->nullable()->after('email');
            }
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('tax_number'); // حذف tax_number فقط
        });
    }
}
