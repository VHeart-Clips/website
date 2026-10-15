<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Management;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use App\Support\Audit\Auditor;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

class AttachToCompilationAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();

        $this
            ->label('admin/resources/clips.actions.attach_to_compilation.label')
            ->translateLabel()
            ->icon(LucideIcon::Link)
            ->schema([
                Select::make('compilation_id')
                    ->label('Compilation')
                    ->searchable()
                    ->options(fn (Clip $record) => $this->attachableCompilations($record)
                        ->orderBy('created_at', 'desc')
                        ->pluck('title', 'id'))
                    ->default(function (Page $livewire): ?int {
                        $id = $livewire->tableFilters['unique_broadcaster_compilation']['compilation_id'] ?? null;

                        /** @var Clip $record */
                        $record = $this->getRecord();

                        return filled($id) && $this->attachableCompilations($record)->whereKey($id)->exists()
                            ? (int) $id
                            : null;
                    })
                    ->preload()
                    ->required(),
                Fieldset::make()
                    ->schema([
                        Toggle::make('claim')
                            ->reactive()
                            ->label('admin/resources/clips.actions.attach_to_compilation.claim')
                            ->translateLabel(),
                        Select::make('status')
                            ->disabled(fn (Get $get): bool => $get('claim') !== true)
                            ->label('admin/resources/clips.actions.attach_to_compilation.status')
                            ->translateLabel()
                            ->options(CompilationClipClaimStatus::class)
                            ->default(CompilationClipClaimStatus::Pending)
                            ->required(),
                    ])->columns(1),
            ])
            ->action(function (Clip $record, array $data, Action $action, Page $livewire): void {
                /** @var Clip\Compilation $compilation */
                $compilation = Clip\Compilation::findOrFail($data['compilation_id']);
                $filtered = filled($livewire->tableFilters['unique_broadcaster_compilation']['compilation_id'] ?? null);

                if (
                    $filtered
                    && $compilation->clips()
                        ->where('clips.broadcaster_id', $record->broadcaster_id)
                        ->whereKeyNot($record->getKey())
                        ->exists()
                ) {
                    Notification::make()
                        ->danger()
                        ->title('Broadcaster already in Compilation')
                        ->body('This compilation already contains a clip from this broadcaster, try again or remove compilation filter.')
                        ->send();

                    $action->halt();
                }

                $pivotData = [
                    'added_by' => auth()->id(),
                    'claim_status' => $data['status'] ?? CompilationClipClaimStatus::Pending,
                    'claimed_by' => $data['claim'] ? auth()->id() : null,
                    'claimed_at' => $data['claim'] ? now() : null,
                ];

                $record->compilations()->attach($compilation, $pivotData);

                Auditor::make()
                    ->event('compilation.clip.attached')
                    ->new($pivotData)
                    ->on($compilation)
                    ->save();
            })
            ->successNotificationTitle(__('admin/resources/clips.notifications.actions.attached_to_compilation'));
    }

    public static function getDefaultName(): ?string
    {
        return 'attachToCompilation';
    }

    protected function attachableCompilations(Clip $record): Builder
    {
        return Clip\Compilation::query()
            ->whereAttachable()
            ->whereNotIn('compilations.id', $record->compilations()->pluck('compilations.id'));
    }
}
