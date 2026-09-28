<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Reports\ReportCategoryDetailsType;
use App\Enums\Reports\ReportReason;
use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (ReportCategory::query()->count() > 0) {
            return;
        }

        DB::transaction(function (): void {
            $categories = [];

            $categories[ReportReason::Other->value] = ReportCategory::create([
                'name' => [
                    'de' => 'Anderes',
                    'en' => 'Other',
                ],
                'summary' => [
                    'de' => 'Falls du nichts Passendes findest, aber denkst, dass wir es uns ansehen sollten.',
                    'en' => "If you can't find a fitting reason but think we should take a look.",
                ],
                'details_type' => ReportCategoryDetailsType::Required,
                'sort_order' => 5,
            ]);

            $categories[ReportReason::Spam->value] = ReportCategory::create([
                'name' => [
                    'de' => 'Spam',
                    'en' => 'Spam',
                ],
                'details_type' => ReportCategoryDetailsType::Optional,
                'sort_order' => 0,
            ]);

            $categories[ReportReason::Harassment->value] = ReportCategory::create([
                'name' => [
                    'de' => 'Belästigung',
                    'en' => 'Harassment',
                ],
                'details_type' => ReportCategoryDetailsType::Optional,
                'sort_order' => 1,
            ]);
            $categories[ReportReason::HateSpeech->value] = ReportCategory::create([
                'name' => [
                    'de' => 'Hassrede',
                    'en' => 'Hate Speech',
                ],
                'details_type' => ReportCategoryDetailsType::Optional,
                'sort_order' => 2,
            ]);
            $categories[ReportReason::AiContent->value] = ReportCategory::create([
                'name' => [
                    'de' => 'AI Inhalte',
                    'en' => 'AI Content',
                ],
                'summary' => [
                    'de' => 'Der Streamer nutzt KI-generierte Inhalte, z.B. für Avatar, Overlay oder Hintergrund.',
                    'en' => 'The streamer uses AI-generated content, e.g. for their avatar, overlay, or background.',
                ],
                'details_type' => ReportCategoryDetailsType::Required,
                'sort_order' => 3,
            ]);

            $categories[ReportReason::ContentUnavailable->value] = ReportCategory::create([
                'name' => [
                    'de' => 'Clip nicht verfügbar',
                    'en' => 'Clip Unavailable',
                ],
                'summary' => [
                    'de' => 'Der Clip wurde auf Twitch entfernt und kann nicht mehr abgespielt werden.',
                    'en' => 'The clip has been removed on Twitch and can no longer be played.',
                ],
                'details_type' => ReportCategoryDetailsType::Disabled,
                'sort_order' => 4,
            ]);

            foreach (ReportReason::cases() as $reason) {
                $category = $categories[$reason->value] ?? null;

                if ($category === null) {
                    continue;
                }

                Report::query()
                    ->withTrashed()
                    ->where('reason', $reason->value)
                    ->update(['category_id' => $category->id]);
            }
        });
    }
}
