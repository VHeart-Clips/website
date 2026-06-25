<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Compilations\RelationManagers\ClipsRelationManager;
use App\Models\Clip;
use App\Support\Audit\Auditor;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class UpdateClaimInfosAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this
            ->label('admin/resources/compilations.relation_managers.clips.actions.update_claim_infos')
            ->translateLabel()
            ->authorize(fn (Clip $clip, ClipsRelationManager $livewire): bool => auth()->user()->can('update', $livewire->getOwnerRecord()))
            ->hidden(fn (ClipsRelationManager $livewire): bool => $livewire->isReadOnly())
            ->icon(LucideIcon::ClipboardEdit)
            ->color('danger')
            ->schema([
                Select::make('claim_status')
                    ->options(CompilationClipClaimStatus::class)
                    ->default(CompilationClipClaimStatus::Pending)
                    ->required()
                    ->columnSpanFull(),
                Select::make('claimed_by')
                    ->searchable()
                    ->optionsLimit(5)
                    ->columnSpanFull()
                    ->relationship(
                        name: 'claimer',
                        titleAttribute: 'name',
                    ),
            ])
            ->action(function (Clip $record, array $data, ClipsRelationManager $livewire): void {
                $updateData = [
                    'claimed_by' => $data['claimed_by'],
                    'claim_status' => $data['claim_status'],
                ];

                $record->compilations()
                    ->updateExistingPivot($livewire->getOwnerRecord()->id, $updateData);

                [$old, $new] = Auditor::resolveSimpleDifferences(
                    [
                        'claimed_by' => $record->claimed_by,
                        'claim_status' => $record->claim_status,
                    ],
                    $updateData,
                );

                Auditor::make()
                    ->event('compilation.clip.updated')
                    ->old(['clip_id' => $record->id, ...$old])
                    ->new(['clip_id' => $record->id, ...$new])
                    ->on($livewire->getOwnerRecord())
                    ->save();
            })
            ->fillForm(fn (Clip $record): array => [
                'claimed_by' => $record->pivot->claimed_by,
                'claim_status' => $record->pivot->claim_status,
            ])
            ->successNotificationTitle(__('admin/resources/compilations.relation_managers.clips.notifications.update_claim_infos'));
    }

    public static function getDefaultName(): ?string
    {
        return 'updateClaimInfos';
    }
}
