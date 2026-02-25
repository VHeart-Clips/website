<?php

declare(strict_types=1);

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
        Schema::create('clips', function (Blueprint $table): void {
            $table->id();
            $table->string('twitch_id')->index();
            $table->string('title');
            $table->string('thumbnail_url')->nullable();
            $table->unsignedBigInteger('broadcaster_id')->index();
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('submitter_id')->index();
            $table->unsignedBigInteger('category_id')->index()->nullable();
            $table->unsignedBigInteger('vod_id')->nullable();
            $table->unsignedBigInteger('vod_offset')->nullable();
            $table->float('duration');
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->string('language')->nullable();
            $table->timestamp('date');
            $table->timestamps();
            $table->softDeletes();
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
