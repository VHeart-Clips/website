<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clips', static function (Blueprint $table): void {
            $table->dropColumn('final_score');
            $table->dropColumn('final_jury_votes');
            $table->dropColumn('final_public_votes');
            $table->dropColumn('final_impressions');

            $table->unsignedBigInteger('score')->default(0)->index();
            $table->unsignedBigInteger('total_jury_votes')->default(0);
            $table->unsignedBigInteger('total_public_votes')->default(0);
            $table->unsignedBigInteger('total_votes')->default(0);
            $table->unsignedBigInteger('total_impressions')->default(0);
        });
    }
};
