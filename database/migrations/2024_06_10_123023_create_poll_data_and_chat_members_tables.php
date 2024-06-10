<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollDataAndChatMembersTables extends Migration
{
    public function up()
    {
        // Create poll_data table
        Schema::create('poll_data', function (Blueprint $table) {
            $table->id();
            $table->string('poll_id');
            $table->json('options');
            $table->integer('total_voter_count');
            $table->string('user_id')->nullable();
            $table->string('username')->nullable();
            $table->timestamps();
        });

        // Create chat_members table
        Schema::create('chat_members', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('user_id');
            $table->string('username')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('poll_data');
        Schema::dropIfExists('chat_members');
    }
}
