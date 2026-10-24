<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->foreignId('user_id')
                ->primary()
                ->constrained()
                ->cascadeOnDelete();

            // should we hide the user on leaderboards? the entry will still be shown but name and image will be hidden
            $table->boolean('show_on_leaderboard')->nullable();

            // should we avoid clips marked as spoiler?
            $table->boolean('avoid_spoilers')->nullable();

            // should we avoid clips of currently released games? null = off, otherwise it should be an enum based value
            $table->string('avoid_new_games')->nullable();

            $table->timestamps();
        });
    }
};
