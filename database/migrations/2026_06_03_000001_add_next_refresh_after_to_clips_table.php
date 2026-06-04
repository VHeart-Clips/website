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
        Schema::table('clips', static function (Blueprint $table): void {
            $table->timestamp('next_refresh_after')->nullable()->index();
        });

        DB::table('clips')
            ->update([
                'next_refresh_after' => now(),
            ]);
    }
};
