<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bans', static function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('admin_id')->index();
            $table->text('reason');

            $table->morphs('bannable');
            $table->dateTime('banned_until')->nullable()->index();

            $table->dateTime('unbanned_at')->nullable()->index();
            $table->unsignedBigInteger('unbanned_by')->nullable()->index();

            $table->timestamps();
        });
    }
};
