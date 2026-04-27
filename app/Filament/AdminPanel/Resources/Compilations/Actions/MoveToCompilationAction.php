<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class MoveToCompilationAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this
            ->label('admin/resources/compilations.relation_managers.clips.actions.move_to_compilation')
            ->translateLabel()
            ->authorize(fn (Clip $clip, $livewire): bool => $clip->claimed_by === null || ($clip->claimed_by === auth()->id() && $clip->status !== CompilationClipClaimStatus::Completed) || auth()->user()->can('update', $livewire->getOwnerRecord()))
            ->icon(LucideIcon::ArrowRight)
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
            ])
            ->action(function (Clip $record, array $data, $livewire): void {
                $record->compilations()
                    ->updateExistingPivot($livewire->getOwnerRecord()->id, [
                        'compilation_id' => $data['compilation_id'],
                    ]);
            })
            ->successNotificationTitle(__('admin/resources/compilations.relation_managers.clips.notifications.moved_to_compilation'));
    }

    public static function getDefaultName(): ?string
    {
        return 'moveToCompilation';
    }
}
