<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip\Compilation;
use Carbon\Constants\UnitValue;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Signature('episodes:create-weekly')]
#[Description('Create episode compilations for current and next week if not exists')]
class CreateWeeklyEpisodesCommand extends Command
{
    public function handle(): void
    {
        $nextCount = Compilation::query()
            ->withTrashed()
            ->where('title', 'like', 'Episode %')
            ->where('type', CompilationType::LongVideo)
            ->where('user_id', 0)
            ->count() + 1;

        $weeks = [
            Carbon::now()->startOfWeek()->next(UnitValue::FRIDAY),
            Carbon::now()->addWeek()->startOfWeek()->next(UnitValue::FRIDAY),
        ];

        foreach ($weeks as $friday) {
            $date = $friday->format('d.m.Y');

            if (Compilation::query()->withTrashed()->where('title', 'like', "%($date)")->exists()) {
                $this->line("Skipping for $date");

                continue;
            }

            $title = "Episode $nextCount ($date)";

            Compilation::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'status' => CompilationStatus::Planned,
                'type' => CompilationType::LongVideo,
                'user_id' => 0,
            ]);

            $this->info("Created: {$title}");
            $nextCount++;
        }
    }
}
