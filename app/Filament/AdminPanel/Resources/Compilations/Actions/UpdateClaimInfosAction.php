<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class UpdateClaimInfosAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this
            ->label('admin/resources/compilations.relation_managers.clips.actions.update_claim_infos')
            ->translateLabel()
            ->authorize(fn (Clip $clip, $livewire): bool => auth()->user()->can('update', $livewire->getOwnerRecord()))
            ->hidden(fn ($livewire): bool => $livewire->isReadOnly())
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
                    ->columnSpanFull()
                    ->relationship(
                        name: 'claimer',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles')
                    )
                    ->preload(),
            ])
            ->action(function (Clip $record, array $data, $livewire): void {
                $record->compilations()
                    ->updateExistingPivot($livewire->getOwnerRecord()->id, [
                        'claimed_by' => $data['claimed_by'],
                        'claim_status' => $data['claim_status'],
                    ]);
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
