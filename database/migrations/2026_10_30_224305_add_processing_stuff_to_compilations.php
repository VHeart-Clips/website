<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compilations', static function (Blueprint $table): void {
            $table->string('processing_status')->nullable();
            $table->uuid('processing_batch_id')->nullable();

            $table->string('zip_path')->nullable();
            $table->unsignedBigInteger('zip_size')->nullable();
            $table->timestamp('zip_created_at')->nullable();
        });
    }
};
