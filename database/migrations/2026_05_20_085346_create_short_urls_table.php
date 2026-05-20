<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->index();
            $table->string('url', 2048);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });
    }
};
