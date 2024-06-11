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
        Schema::create('poll_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('poll_id');
            $table->bigInteger('user_id');
            $table->bigInteger('chat_id');
            $table->string('username');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('date')->nullable();
            $table->json('option_ids')->nullable()->default(json_encode([0]));
        });
    }

    public function down()
    {
        Schema::dropIfExists('poll_answers');
    }
};
