<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comments
        Schema::create(config('commentions.tables.comments', 'comments'), function (Blueprint $table): void {
            $table->id();
            $table->morphs('author');
            $table->morphs('commentable');
            $table->text('body');
            $table->timestamps();
        });

        // Reactions
        Schema::create(config('commentions.tables.comment_reactions', 'comment_reactions'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comment_id')->constrained(config('commentions.tables.comments'))->cascadeOnDelete();
            $table->morphs('reactor');

            if (config('database.default') === 'mysql') {
                $table->string('reaction', 50)->collation('utf8mb4_bin');
            } else {
                $table->string('reaction', 50);
            }

            $table->timestamps();
        });

        // Subscriptions
        Schema::create(config('commentions.tables.comment_subscriptions', 'comment_subscriptions'), function (Blueprint $table): void {
            $table->id();
            $table->morphs('subscribable');
            $table->morphs('subscriber');
            $table->timestamps();

            $table->unique([
                'subscribable_type', 'subscribable_id', 'subscriber_type', 'subscriber_id',
            ], 'commentions_subscriptions_unique');
        });
    }
};
