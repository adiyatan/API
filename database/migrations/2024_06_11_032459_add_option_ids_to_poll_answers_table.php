<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('poll_answers', function (Blueprint $table) {
            $table->json('option_ids')->nullable()->default(json_encode([0]));
        });
    }

    public function down()
    {
        Schema::table('poll_answers', function (Blueprint $table) {
            $table->dropColumn('option_ids');
        });
    }
};
