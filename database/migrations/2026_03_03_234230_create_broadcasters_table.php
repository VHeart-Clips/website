<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            $table->timestamp('onboarded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id')->references('id')->on('users')->cascadeOnDelete();
        });

        // BroadcasterConsentLog
        Schema::create('broadcaster_consent_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('broadcaster_id')->index();

            $table->jsonb('state');

            $table->unsignedBigInteger('changed_by')->index();
            $table->string('change_reason')->nullable();

            $table->timestamp('changed_at');
            $table->string('checksum', 73);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
            CREATE OR REPLACE FUNCTION prevent_consent_log_mutation()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION \'broadcaster_consent_logs is append-only\';
            END;
            $$ LANGUAGE plpgsql
        ');

            DB::statement('
            CREATE TRIGGER consent_logs_immutable
            BEFORE UPDATE OR DELETE ON broadcaster_consent_logs
            FOR EACH ROW EXECUTE FUNCTION prevent_consent_log_mutation()
        ');
        }

        // BroadcasterTeamMember
        Schema::create('broadcaster_team_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('broadcaster_id');
            $table->unsignedBigInteger('user_id')->index();

            $table->jsonb('permissions')->nullable();
            $table->timestamps();

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

            $table->foreign('broadcaster_id')->references('id')->on('broadcasters')->cascadeOnDelete();

            $table->unique(['broadcaster_id', 'filterable_type', 'filterable_id'], 'broadcaster_filter_unique_index');
        });

        // Broadcaster/RemovalRequest
        Schema::create('removal_requests', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('broadcaster_id');
            $table->foreignId('clip_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('status')->default(0);
            $table->text('details')->nullable();

            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('claimed_by')->nullable()->constrained('users');

            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['broadcaster_id', 'status']);
            $table->index('clip_id');
        });
    }
};
