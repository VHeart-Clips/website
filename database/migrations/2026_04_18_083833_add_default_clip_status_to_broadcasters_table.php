<?php

declare(strict_types=1);

use App\Enums\Clips\ClipStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broadcasters', static function (Blueprint $table): void {
            $table->unsignedTinyInteger('default_clip_status')->default(ClipStatus::Unknown);
        });
    }
};
