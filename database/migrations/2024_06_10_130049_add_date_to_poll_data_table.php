<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateToPollDataTable extends Migration
{
    public function up()
    {
        Schema::table('poll_data', function (Blueprint $table) {
            $table->timestamp('date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('poll_data', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
}
