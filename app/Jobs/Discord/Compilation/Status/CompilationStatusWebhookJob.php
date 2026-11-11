<?php

declare(strict_types=1);

namespace App\Jobs\Discord\Compilation\Status;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Filament\AdminPanel\Resources\Compilations\CompilationResource;
use App\Jobs\Discord\BaseDiscordWebhookJob;
use App\Models\Audit;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use JustinKluever\DiscordWebhookBuilder\Components\Button;
use JustinKluever\DiscordWebhookBuilder\Components\Container;
use JustinKluever\DiscordWebhookBuilder\Components\Section;
use JustinKluever\DiscordWebhookBuilder\Components\Separator;
use JustinKluever\DiscordWebhookBuilder\Components\TextDisplay;
use JustinKluever\DiscordWebhookBuilder\Enums\Components\ButtonStyle;
use JustinKluever\DiscordWebhookBuilder\Enums\Support\MessageFlag;
use JustinKluever\DiscordWebhookBuilder\Support\Webhook\AllowedMentions;
use JustinKluever\DiscordWebhookBuilder\Webhook;

#[DeleteWhenMissingModels]
#[WithoutRelations]
#[DebounceFor(30)]
class CompilationStatusWebhookJob extends BaseDiscordWebhookJob
{
    public const int TARGET_COMPILATION_TIME = 600;

    private const string RELEASE_DATE_PATTERN = '/\((\d{2}\.\d{2}\.\d{4})\)/';

    private const array EVENT_LABELS = [
        'compilation.clip.attached' => 'Clip hinzugefügt',
        'compilation.clip.updated' => 'Clip aktualisiert',
        'compilation.clip.claimed' => 'Clip beansprucht',
        'compilation.clip.moved' => 'Clip verschoben',
        'created' => 'Compilation erstellt',
    ];

    public function __construct(
        private readonly Compilation $compilation,
    ) {}

    public function debounceId(): string
    {
        return $this->cacheKey('debounce').':'.$this->compilation->id;
    }

    protected function shouldRun(): bool
    {
        $release = $this->getPlannedReleaseDate();

        return filled($this->compilation->discord_message_id)
            || ! $release instanceof CarbonImmutable
            || $release->subDays(6)->isPast();
    }

    protected function getPayload(): Webhook
    {
        $release = $this->getPlannedReleaseDate();

        $byStatus = static fn (CompilationClipClaimStatus $status): callable => static fn (EloquentBuilder $query): EloquentBuilder => $query
            ->whereNull('removed_at')
            ->where('compilation_clip.claim_status', $status);

        $compilation = $this->compilation
            ->loadCount([
                'clips' => static fn (EloquentBuilder $query): EloquentBuilder => $query->whereNull('removed_at'),
                'clips as in_progress_clips_count' => $byStatus(CompilationClipClaimStatus::InProgress),
                'clips as completed_clips_count' => $byStatus(CompilationClipClaimStatus::Completed),
            ])
            ->loadSum([
                'clips as in_progress_duration' => $byStatus(CompilationClipClaimStatus::InProgress),
                'clips as completed_duration' => $byStatus(CompilationClipClaimStatus::Completed),
            ], 'compilation_clip.file_duration');

        $totalClips = $compilation->clips_count;
        $clipsCompleted = (int) $compilation->getAttribute('completed_clips_count');
        $durationCompleted = (int) $compilation->getAttribute('completed_duration');

        if ($compilation->status->isPublic()) {
            $components = [
                TextDisplay::make($this->getContributions()),
            ];
        } elseif ($totalClips > 0) {
            $durationText = $this->getDurationText($durationCompleted).' / '.$this->getDurationText(self::TARGET_COMPILATION_TIME);

            $components = [
                $this->getProgressLine('Clips', "$clipsCompleted / $totalClips", $clipsCompleted, (int) $compilation->getAttribute('in_progress_clips_count'), $totalClips),
                $this->getProgressLine('Länge', $durationText, $durationCompleted, (int) $compilation->getAttribute('in_progress_duration'), self::TARGET_COMPILATION_TIME),
                Separator::make(),
                TextDisplay::make($this->getRecentActivity()),
            ];
        } else {
            $components = [
                TextDisplay::make('Bisher keine Aktivität.'),
            ];
        }

        $title = mb_trim(
            $release instanceof CarbonImmutable
                ? Str::replaceMatches(self::RELEASE_DATE_PATTERN, '', $compilation->title)
                : $compilation->title
        );
        $releaseLabel = $release instanceof CarbonImmutable
            ? $this->getDiscordTimestamp($release)
            : 'Irgendwann';

        return Webhook::make()
            ->flag(MessageFlag::IS_COMPONENTS_V2)
            ->allowedMentions(AllowedMentions::none())
            ->component(
                Container::make(
                    Section::make(
                        Button::make()
                            ->label('Anzeigen')
                            ->url(CompilationResource::getUrl('view', ['record' => $compilation], panel: 'admin'))
                            ->style(ButtonStyle::Link),
                        TextDisplay::make("### $title\n-# **{$compilation->status->getLabel()}** - $releaseLabel"),
                    ),
                    ...$components,
                ),
            );
    }

    protected function handleResponse(Response $response): void
    {
        if (filled($this->compilation->discord_message_id)) {
            return;
        }

        $this->compilation->update([
            'discord_message_id' => (int) $response->json('id'),
        ]);
    }

    protected function handleWebhookNotFound(Response $response): ?bool
    {
        Log::debug('Compilation Message got removed, defusing', [
            'webhook_id' => $this->getWebhookId(),
            'message_id' => $this->getWebhookMessageId(),
        ]);

        $this->compilation->update([
            'discord_message_id' => null,
        ]);

        return true;
    }

    protected function getWebhook(): string
    {
        $base = config('services.discord.webhooks.compilation');

        return filled($this->compilation->discord_message_id)
            ? "$base/messages/{$this->compilation->discord_message_id}?with_components=true&wait=false"
            : "$base?with_components=true&wait=true";
    }

    /**
     * Twitch allows `_` in the name which will be parsed as markdown in discord
     */
    private function escapeTwitchName(string $name): string
    {
        return str_replace('_', '\_', $name);
    }

    private function getDurationText(int $seconds): string
    {
        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    private function getProgressBar(int $completed, int $in_progress, int $total): string
    {
        $width = 28;
        $total = max(1, $total);
        $completed = clamp($completed, 0, $total);
        $in_progress = clamp($in_progress, 0, $total - $completed);

        $wip = $in_progress > 0
            ? max(1, (int) round($in_progress / $total * $width))
            : 0;
        $done = min(
            (int) round($completed / $total * $width),
            $width - $wip
        );

        return '['
            .Str::repeat('=', $done)
            .Str::repeat('>', $wip)
            .Str::repeat('-', $width - $done - $wip)
            .']';
    }

    private function getProgressLine(string $label, string $text, int $completed, int $inProgress, int $total): TextDisplay
    {
        $percentage = Str::padLeft(Number::percentage($completed / $total * 100, locale: 'de'), 5);
        $bar = $this->getProgressBar($completed, $inProgress, $total);

        return TextDisplay::make("`$label: ".Str::padRight($text, 15)." $bar $percentage`");
    }

    private function getRecentActivity(): string
    {
        $lines = $this->compilation
            ->audits()
            ->with('causer')
            ->where('causer_type', new User()->getMorphClass())
            ->whereIn('event', array_keys(self::EVENT_LABELS))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Audit $audit): string {
                $causer = $this->escapeTwitchName($audit->causer?->name ?? 'Unbekannt');
                $event = self::EVENT_LABELS[$audit->event] ?? $audit->event;

                return "-# _{$causer}_: $event {$this->getDiscordTimestamp($audit->created_at)}";
            });

        return $lines->isEmpty()
            ? '-# Bisher keine Aktivität'
            : $lines->implode("\n");
    }

    private function getContributions(): string
    {
        $contributors = CompilationClip::query()
            ->with('claimer')
            ->whereBelongsTo($this->compilation)
            ->where('claim_status', CompilationClipClaimStatus::Completed)
            ->get()
            ->pluck('claimer.name')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys();

        if ($contributors->isEmpty()) {
            return '-# Es gibt keine Kontributoren die ich nennen kann :(';
        }

        return '-# Danke an '.$contributors
            ->map($this->escapeTwitchName(...))
            ->implode(', ');
    }

    private function getDiscordTimestamp(CarbonInterface $dateTime): string
    {
        return "<t:$dateTime->timestamp:R>";
    }

    private function getPlannedReleaseDate(): ?CarbonImmutable
    {
        if (! preg_match(self::RELEASE_DATE_PATTERN, $this->compilation->title, $match)) {
            return null;
        }

        return rescue(
            static fn (): CarbonImmutable => CarbonImmutable::createFromFormat('d.m.Y', $match[1])->setTime(16, 0),
            report: false,
        );
    }
}
