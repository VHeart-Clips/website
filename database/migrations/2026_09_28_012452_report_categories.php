<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('report_categories')->restrictOnDelete();

            $table->jsonb('name');
            $table->jsonb('summary')->nullable();
            $table->jsonb('description')->nullable();

            $table->jsonb('reportable_types')->nullable();
            $table->boolean('is_note')->default(false);
            $table->string('details_type'); // ReportCategoryDetailsType enum
            $table->integer('sort_order');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->foreignId('category_id')
                ->nullable() // TODO: make sure to remove the nullable state in future migrations after everything is properly migrated
                ->after('user_id')
                ->constrained('report_categories')
                ->restrictOnDelete();

            // TODO: make sure to remove in the future
            $table->unsignedInteger('reason')
                ->nullable()
                ->comment('deprecated by category_id, will be removed in future migrations')
                ->change();
        });
    }
};
