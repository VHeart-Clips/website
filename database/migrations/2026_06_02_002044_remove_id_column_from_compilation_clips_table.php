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
        DB::table('audits')
            ->where('auditable_type', 'compilation_clip')
            ->delete();

        Schema::table('compilation_clip', function (Blueprint $table): void {
            $table->dropPrimary();
            $table->dropColumn('id');
        });
    }
};
