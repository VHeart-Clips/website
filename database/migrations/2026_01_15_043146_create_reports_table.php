<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->references('id')->on('users');

            $table->foreignId('claimed_by')
                ->nullable()
                ->references('id')->on('users');

            $table->foreignId('resolved_by')
                ->nullable()
                ->references('id')->on('users');

            $table->morphs('reportable');

            // ReportReason Enum
            $table->unsignedInteger('reason')->index();
            $table->text('description')
                ->nullable();

            // ReportStatus Enum, default "Pending"
            $table->unsignedInteger('status')->default(0)->index();

            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['reportable_id', 'reportable_type']);
        });
    }
};
