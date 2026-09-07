<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rating on comments
        Schema::table(config('commentions.tables.comments', 'comments'), function (Blueprint $table): void {
            if (! Schema::hasColumn(config('commentions.tables.comments', 'comments'), 'rating')) {
                $table->unsignedTinyInteger('rating')->nullable()->after('body');
            }
        });

        // Attachments
        Schema::create(config('commentions.tables.comment_attachments', 'comment_attachments'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comment_id')->constrained(config('commentions.tables.comments', 'comments'))->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }
};
