<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Management;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\FeatureFlag;
use App\Enums\Filament\LucideIcon;
use App\Filament\Forms\Components\CustomFileUpload;
use App\Models\Clip;
use App\Support\Audit\Auditor;
use App\Support\FeatureFlag\Feature;
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
                    ->live()
                    ->required(),
                Fieldset::make()
                    ->visible(fn (Get $get): bool => $get('compilation_id') !== null)
                    ->schema([
                        Toggle::make('claim')
                            ->reactive()
                            ->label('admin/resources/clips.actions.attach_to_compilation.claim')
                            ->translateLabel(),
                        Select::make('status')
                            ->live(condition: fn (): bool => Feature::isActive(FeatureFlag::ClipUpload))
                            ->disabled(fn (Get $get): bool => $get('claim') !== true)
                            ->label('admin/resources/clips.actions.attach_to_compilation.status')
                            ->translateLabel()
                            ->options(CompilationClipClaimStatus::class)
                            ->default(CompilationClipClaimStatus::Pending)
                            ->required(),
                        CustomFileUpload::make('file_path')
                            ->disk('compilation_clips_archive')
                            ->maxSize(257 * 1024)
                            ->required(fn (Get $get): bool => $get('status') === CompilationClipClaimStatus::Completed)
                            ->visible(fn (Get $get): bool => $get('status') === CompilationClipClaimStatus::Completed)
                            ->hidden(fn (): bool => ! Feature::isActive(FeatureFlag::ClipUpload))
                            ->getDirectoryUsing(fn (Get $get): int => $get('compilation_id'))
                            ->acceptedFileTypes(['video/mp4' => 'mp4']),
                    ])->columns(1),
            ])
            ->action(function (Clip $record, array $data): void {
                $pivotData = [
                    'added_by' => auth()->id(),
                    'claim_status' => $data['status'] ?? CompilationClipClaimStatus::Pending,
                    'claimed_by' => $data['claim'] ? auth()->id() : null,
                    'claimed_at' => now(),
                ];

                if (Feature::isActive(FeatureFlag::ClipUpload) && array_key_exists('file_path', $data)) {
                    $pivotData['file_path'] = $data['file_path'];
                }

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
