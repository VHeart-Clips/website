<?php

declare(strict_types=1);

namespace App\Jobs\Discord\Reports;

use App\Enums\Reports\ReportStatus;
use App\Jobs\Discord\BaseDiscordWebhookJob;
use App\Models\Report;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JustinKluever\DiscordWebhookBuilder\Components\ActionRow;
use JustinKluever\DiscordWebhookBuilder\Components\Button;
use JustinKluever\DiscordWebhookBuilder\Components\Container;
use JustinKluever\DiscordWebhookBuilder\Components\Separator;
use JustinKluever\DiscordWebhookBuilder\Components\TextDisplay;
use JustinKluever\DiscordWebhookBuilder\Enums\Components\ButtonStyle;
use JustinKluever\DiscordWebhookBuilder\Enums\Support\MessageFlag;
use JustinKluever\DiscordWebhookBuilder\Support\Webhook\AllowedMentions;
use JustinKluever\DiscordWebhookBuilder\Webhook;

#[DeleteWhenMissingModels]
#[WithoutRelations]
#[Queue('moderation')]
#[DebounceFor(30)]
class ReportWebhookJob extends BaseDiscordWebhookJob
{
    public function __construct(
        private readonly Report $report,
    ) {}

    public function debounceId(): string
    {
        return $this->cacheKey('debounce').':'.$this->report->id;
    }

    protected function shouldRun(): bool
    {
        $report = $this->report;

        // We do not care about reports that got automatically resolved by system
        if (
            $report->status === ReportStatus::Resolved
            && $report->discord_message_id === null
            && $report->resolved_by === 0
        ) {
            Log::debug('Report got handled by System before we knew about it, ignoring.');

            return false;
        }

        return true;
    }

    protected function getRequest(): PendingRequest|Response
    {
        if ($this->report->discord_message_id === null) {
            return parent::getRequest();
        }

        return Http::timeout(5)->patch($this->getWebhook(), $this->getPayload());
    }

    protected function getPayload(): Webhook
    {
        $currentStatus = $this->report->status->name;
        $this->report->loadMissing([
            'resolver',
            'claimer',
            'reportable',
        ]);

        if ($this->report->resolver instanceof User) {
            $currentStatus = 'Resolved by '.$this->report->resolver->name." ({$this->getDiscordTimestamp($this->report->resolved_at)})";
        } elseif ($this->report->claimer instanceof User) {
            $currentStatus = 'Claimed by '.$this->report->claimer->name." ({$this->getDiscordTimestamp($this->report->claimed_at)})";
        }

        return Webhook::make()
            ->flag(MessageFlag::IS_COMPONENTS_V2)
            ->allowedMentions(AllowedMentions::none()->roles('1494691682422226996'))
            ->component(
                Container::make(
                    TextDisplay::make("### Report of type `{$this->report->reason->name}`"),
                    TextDisplay::make($this->report->description ?? 'No Details'),
                    Separator::make(),
                    TextDisplay::make("-# $currentStatus • Created {$this->getDiscordTimestamp($this->report->created_at)} • <@&1494691682422226996>")
                )
                    ->accentColor($this->report->status->getDiscordColor()),
                ActionRow::make(
                    $this->makeViewReportButton(),
                    $this->makeViewReportableButton(),
                    $this->makeViewAllReportsButton(),
                )
            );
    }

    protected function handleResponse(Response $response): void
    {
        if ($this->report->discord_message_id !== null) {
            return;
        }

        $messageId = (int) $response->json('id');

        $this->report->update([
            'discord_message_id' => $messageId,
        ]);
    }

    protected function handleWebhookNotFound(Response $response): ?bool
    {
        Log::debug('Report Message got removed, defusing', [
            'webhook_id' => $this->getWebhookId(),
            'message_id' => $this->getWebhookMessageId(),
        ]);

        $this->report->update([
            'discord_message_id' => null,
        ]);

        return true;
    }

    protected function getWebhook(): string
    {
        $base = config('services.discord.webhooks.moderation');

        return $this->report->discord_message_id === null
            ? $base.'?with_components=true&wait=true'
            : $base."/messages/{$this->report->discord_message_id}?with_components=true&wait=false";
    }

    private function makeViewReportButton(): Button
    {
        return Button::make()
            ->label('View Report')
            ->style(ButtonStyle::Link)
            ->url(Filament::getPanel('admin')->getResourceUrl($this->report, 'view'));
    }

    private function makeViewReportableButton(): Button
    {
        $reportable = (
            $this->report->relationLoaded('reportable')
            && $this->report->reportable instanceof Model
        )
            ? $this->report->reportable
            : $this->report->reportable()->withTrashed()->first();

        $url = $reportable
            ? Filament::getPanel('admin')->getResourceUrl($reportable, 'view')
            : null;

        return Button::make()
            ->label('View '.Str::title($this->report->reportable_type))
            ->style(ButtonStyle::Link)
            ->url($url ?? 'https://vheart.net')
            ->disabled($url === null);
    }

    private function makeViewAllReportsButton(): Button
    {
        return Button::make()
            ->label('View All Reports')
            ->style(ButtonStyle::Link)
            ->url(Filament::getPanel('admin')->getResourceUrl(Report::class));
    }

    private function getDiscordTimestamp(CarbonInterface $dateTime): string
    {
        return "<t:$dateTime->timestamp:R>";
    }
}
