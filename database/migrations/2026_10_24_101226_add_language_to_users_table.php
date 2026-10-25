<?php

declare(strict_types=1);

use App\Enums\UserSettings\LanguagePreference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            /**
             * because this preference is required on every page load this will be on the users table directly
             *
             * @see LanguagePreference
             */
            $table->string('language')->nullable();
        });
    }
};
