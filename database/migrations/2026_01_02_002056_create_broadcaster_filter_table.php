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
        Schema::create('broadcaster_filter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broadcaster_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('allowed');
            $table->timestamps();

            $table->foreign('broadcaster_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcaster_filter');
    }
};
