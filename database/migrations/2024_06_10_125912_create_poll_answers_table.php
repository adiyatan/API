<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('poll_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('poll_id');
            $table->bigInteger('user_id');
            $table->string('username');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('poll_answers');
    }
}
