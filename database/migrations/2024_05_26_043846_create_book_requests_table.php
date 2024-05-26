<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('book_id');
            $table->string('start_date');
            $table->string('end_date');
            $table->enum('status', ['on loan', 'returned', 'lost', 'damaged']);
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('member_tubes')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('bookstubes')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
