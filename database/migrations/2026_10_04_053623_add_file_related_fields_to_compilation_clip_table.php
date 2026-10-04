<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compilation_clip', function (Blueprint $table) {
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable()->index();
            $table->unsignedBigInteger('file_duration')->nullable()->index();
            $table->json('file_metadata')->nullable();
        });
    }
};
