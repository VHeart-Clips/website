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
        Schema::create('clips', function (Blueprint $table) {
            $table->id();
            $table->string('twitch_id');
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedBigInteger('broadcaster_id');
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('submitter_id');
            $table->unsignedBigInteger('game_id')->nullable();
            $table->unsignedBigInteger('vod_id')->nullable();
            $table->unsignedBigInteger('vod_offset')->nullable();
            $table->float('duration');
            $table->string('status')->nullable(); // für flag feature
            $table->string('language')->nullable();
            $table->timestamp('date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('broadcaster_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('submitter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clips');
    }
};
