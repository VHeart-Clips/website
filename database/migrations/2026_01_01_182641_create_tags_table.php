<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->jsonb('name');
        });

        Schema::create('clip_tags', function (Blueprint $table): void {
            $table->foreignId('clip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['clip_id', 'tag_id']); // get tags from clips
            $table->index(['tag_id', 'clip_id']); // get clips from tags
        });
    }
};
