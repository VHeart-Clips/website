<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('url');
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });
    }
};
