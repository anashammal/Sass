<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToStoresTable extends Migration
{
    public function up()
    {
        // !! تم إفراغ هذه الدالة لتجنب تكرار عمود 'status' !!
    }

    public function down()
    {
        // لا نحتاج لـ drop هنا لأن الملفات الأقدم ستتكفل بذلك عند الـ migrate:reset
    }
}
