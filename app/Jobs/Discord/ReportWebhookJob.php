<?php

declare(strict_types=1);

namespace App\Jobs\Discord;

use App\Models\Report;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JustinKluever\DiscordWebhookBuilder\Components\ActionRow;
use JustinKluever\DiscordWebhookBuilder\Components\Button;
use JustinKluever\DiscordWebhookBuilder\Components\Container;
use JustinKluever\DiscordWebhookBuilder\Components\Separator;
use JustinKluever\DiscordWebhookBuilder\Components\TextDisplay;
use JustinKluever\DiscordWebhookBuilder\Enums\Components\ButtonStyle;
use JustinKluever\DiscordWebhookBuilder\Enums\Support\MessageFlag;
use JustinKluever\DiscordWebhookBuilder\Support\Color;
use JustinKluever\DiscordWebhookBuilder\Support\Webhook\AllowedMentions;
use JustinKluever\DiscordWebhookBuilder\Webhook;

#[DeleteWhenMissingModels]
#[Queue('moderation')]
class ReportWebhookJob extends BaseDiscordWebhookJob
{
    public function __construct(
        private readonly Report $report,
    ) {}

    protected function getPayload(): Webhook
    {
        $currentStatus = $this->report->status->name;

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
                    ->accentColor(Color::fromHex('#e71d73')),
                ActionRow::make(
                    Button::make()
                        ->label('View Report')
                        ->style(ButtonStyle::Link)
                        ->url(Filament::getPanel('admin')->getResourceUrl($this->report, 'view')),
                    Button::make()
                        ->label('View '.Str::title($this->report->reportable_type))
                        ->style(ButtonStyle::Link)
                        ->url(Filament::getPanel('admin')->getResourceUrl($this->report->reportable, 'view')),
                    Button::make()
                        ->label('View All Reports')
                        ->style(ButtonStyle::Link)
                        ->url(Filament::getPanel('admin')->getResourceUrl($this->report))
                )
            );
    }

    protected function handleResponse(Response $response): void
    {
        $messageId = $response->json('id');

        Log::debug('Discord webhook response for report', [
            'report_id' => $this->report->id,
            'message_id' => $messageId,
        ]);
    }

    protected function getWebhook(): string
    {
        return config('services.discord.webhooks.moderation').'?with_components=true&wait=true';
    }

    private function getDiscordTimestamp(CarbonInterface $dateTime): string
    {
        return "<t:$dateTime->timestamp:R>";
    }
}
