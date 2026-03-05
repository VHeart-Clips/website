<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Broadcaster
        Schema::create('broadcasters', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();

            $table->jsonb('consent')->nullable();
            $table->jsonb('twitch_mod_permissions')->nullable();

            $table->boolean('submit_user_allowed')->default(false);
            $table->boolean('submit_mods_allowed')->default(false);
            $table->boolean('submit_vip_allowed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id')->references('id')->on('users')->cascadeOnDelete();
        });

        // BroadcasterTeamMember
        Schema::create('broadcaster_team_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('broadcaster_id');
            $table->unsignedBigInteger('user_id')->index();

            $table->jsonb('permissions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('broadcaster_id')->references('id')->on('broadcasters')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['broadcaster_id', 'user_id'], 'broadcaster_team_members_unique_index');
        });

        // BroadcasterSubmissionFilter
        Schema::create('broadcaster_submission_filters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('broadcaster_id');

            $table->morphs('filterable');
            $table->boolean('state')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('broadcaster_id')->references('id')->on('broadcasters')->cascadeOnDelete();

            $table->unique(['broadcaster_id', 'filterable_type', 'filterable_id'], 'broadcaster_filter_unique_index');
        });
    }
};
