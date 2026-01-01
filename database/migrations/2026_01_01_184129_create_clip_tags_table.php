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
        Schema::create('clip_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('clip_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->foreign('clip_id')->references('id')->on('clips')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clip_tag');
    }
};
