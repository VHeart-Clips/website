<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Management;

use App\Enums\Clips\ClipFeedbackOption;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use App\Models\User;
use App\Support\Audit\Auditor;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;

class ClipFeedbackAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->modalDescription('This clip will be removed and the broadcaster will be notified with your feedback.')
            ->authorize('feedback')
            ->icon(LucideIcon::MessagesSquare)
            ->label('Clip Feedback')
            ->requiresConfirmation()
            ->color('warning')
            ->schema([
                CheckboxList::make('options')
                    ->label('Feedback')
                    ->options(ClipFeedbackOption::class)
                    ->required()
                    ->live(),

                Textarea::make('note')
                    ->label('Additional note')
                    ->hint('keep it short')
                    ->required()
                    ->maxLength(255)
                    ->rows(3)
                    ->visible(fn (Get $get): bool => in_array(ClipFeedbackOption::Other, $get('options'), true)),
            ])
            ->action(function (array $data, Clip $record): void {
                /** @var list<ClipFeedbackOption> $options */
                $options = $data['options'];
                /** @var ?string $note */
                $note = $data['note'] ?? null;
                $record->loadMissing('broadcaster.user');

                if ($user = $record->broadcaster?->user ?? User::find($record->broadcaster_id)) {
                    $body = collect($options)
                        ->reject(fn (ClipFeedbackOption $r): bool => $r === ClipFeedbackOption::Other)
                        ->map(fn (ClipFeedbackOption $r): string => $r->getLabel())
                        ->when($note, fn (Collection $collection) => $collection->push($note))
                        ->join(', ');

                    Notification::make()
                        ->title("Clip feedback: {$record->title}")
                        ->body($body)
                        ->warning()
                        ->actions([
                            Action::make('view')
                                ->label('View on Twitch')
                                ->button()
                                ->url($record->getClipUrl(), shouldOpenInNewTab: true)
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($user);
                }

                Auditor::make()
                    ->causer(auth()->user())
                    ->on($record)
                    ->event('feedback')
                    ->new([
                        'options' => $options,
                        'note' => $note,
                    ])
                    ->save();

                $record->delete();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'clipFeedback';
    }
}
