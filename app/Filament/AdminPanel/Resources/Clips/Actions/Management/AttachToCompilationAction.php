<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Management;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use App\Support\Audit\Auditor;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
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
                    ->options(fn (Clip $record) => Clip\Compilation::query()
                        ->orderBy('created_at', 'desc')
                        ->whereNotIn('id', $record->compilations()->pluck('compilations.id'))
                        ->whereNotIn('compilations.status', CompilationStatus::getVoteDisabledCases())
                        ->whereNot(fn (Builder $builder) => $builder->where('compilations.status', CompilationStatus::Internal)
                            ->whereNot('compilations.user_id', auth()->id()))
                        ->pluck('title', 'id'))
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
            ->action(function (Clip $record, array $data): void {
                $pivotData = [
                    'added_by' => auth()->id(),
                    'claim_status' => $data['status'] ?? CompilationClipClaimStatus::Pending,
                    'claimed_by' => $data['claim'] ? auth()->id() : null,
                    'claimed_at' => now(),
                ];

                $record->compilations()->attach($data['compilation_id'], $pivotData);

                Auditor::make()
                    ->event('compilation.clip.attached')
                    ->new($pivotData)
                    ->on(Clip\Compilation::find($data['compilation_id']))
                    ->save();
            })
            ->successNotificationTitle(__('admin/resources/clips.notifications.actions.attached_to_compilation'));
    }

    public static function getDefaultName(): ?string
    {
        return 'attachToCompilation';
    }
}
