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
        Schema::create('compilations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status'); // CompilationStatus
            $table->string('type'); // CompilationType

            $table->text('description')->nullable();
            $table->string('youtube_url')->nullable();

            // If set, it will try to fill the compilation with enough clips to reach X seconds total duration
            // If any clip get removed it and this is still set, it will try to fill it again
            $table->unsignedInteger('auto_fill_seconds')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('clip_compilation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clip_id')->constrained();
            $table->foreignId('compilation_id')->constrained()->cascadeOnDelete();

            // Cutter that claimed this Clip in the compilation, only the current claimer can change the status
            $table->foreignId('claimed_by')->nullable()->references('id')->on('users');
            $table->timestamp('claimed_at')->nullable();


            $table->unsignedInteger('status')->index(); // CompilationClipStatus

            // This Clip has been removed after publishing the video, we still need to keep track of it.
            $table->timestamp('removed_at')->nullable();

            $table->timestamps();
            $table->unique(['compilation_id', 'clip_id']);
            $table->index(['compilation_id', 'clip_id']);
        });
    }
};
