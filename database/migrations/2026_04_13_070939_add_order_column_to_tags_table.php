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
        DB::unprepared('CREATE SEQUENCE IF NOT EXISTS tags_order_seq');

        Schema::table('tags', function (Blueprint $table): void {
            $table->unsignedBigInteger('order')->default(DB::raw("nextval('tags_order_seq')"));
            $table->index(['order', 'id']);
        });

        DB::unprepared('ALTER SEQUENCE tags_order_seq OWNED BY tags.order');
    }
};
