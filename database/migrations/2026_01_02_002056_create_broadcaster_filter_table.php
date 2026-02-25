<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('broadcaster_filters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('broadcaster_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->morphs('filterable');
            $table->boolean('state')->index();

            $table->index(['broadcaster_id', 'filterable_type', 'filterable_id']);
            $table->unique(['broadcaster_id', 'filterable_type', 'filterable_id'], 'unique_broadcaster_filter');
        });
    }
};
