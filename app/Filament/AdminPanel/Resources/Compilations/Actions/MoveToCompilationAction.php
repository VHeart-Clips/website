<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Compilations\RelationManagers\ClipsRelationManager;
use App\Models\Clip;
use App\Support\Audit\Auditor;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class MoveToCompilationAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this
            ->label('admin/resources/compilations.relation_managers.clips.actions.move_to_compilation')
            ->translateLabel()
            ->authorize(fn (Clip $clip, ClipsRelationManager $livewire): bool => $clip->claimed_by === null || ($clip->claimed_by === auth()->id() && $clip->status !== CompilationClipClaimStatus::Completed) || auth()->user()->can('update', $livewire->getOwnerRecord()))
            ->icon(LucideIcon::ArrowRight)
            ->schema([
                Select::make('compilation_id')
                    ->label('Compilation')
                    ->searchable()
                    ->options(fn (Clip $record) => Clip\Compilation::query()
                        ->orderBy('created_at', 'desc')
                        ->whereNotIn('id', $record->compilations()->pluck('compilations.id'))
                        ->whereNotIn('compilations.status', CompilationStatus::getVoteDisabledCases())
                        ->whereNot(fn (Builder $builder): Builder => $builder->where('compilations.status', CompilationStatus::Internal)
                            ->whereNot('compilations.user_id', auth()->id()))
                        ->pluck('title', 'id'))
                    ->preload()
                    ->required(),
            ])
            ->action(function (Clip $record, array $data, ClipsRelationManager $livewire): void {
                $pivotAlreadyExists = Clip\CompilationClip::query()
                    ->where('compilation_id', $data['compilation_id'])
                    ->where('clip_id', $record->id)
                    ->exists();

                if ($pivotAlreadyExists) {
                    Notification::make('already-in-compilation')
                        ->title('Could not move Clip')
                        ->body('Clip is already in the selected Compilation')
                        ->warning()
                        ->send();

                    $this->halt();
                }

                try {
                    $updatedCount = Clip\CompilationClip::query()
                        ->where('compilation_id', $livewire->getOwnerRecord()->getKey())
                        ->where('clip_id', $record->id)
                        ->update([
                            'compilation_id' => $data['compilation_id'],
                        ]);

                    if ($updatedCount === 0) {
                        Notification::make('did-not-update-properly')
                            ->title('Could not move Clip')
                            ->body('There was an error while moving clip, please contact IT if the issue persists')
                            ->danger()
                            ->send();

                        Log::warning('Did not update CompilationClip properly', [
                            'clip_id' => $record->id,
                            'current_compilation_id' => $livewire->getOwnerRecord()->getKey(),
                            'target_compilation_id' => $data['compilation_id'],
                            'user_id' => auth()->id(),
                        ]);

                        $this->halt();
                    }

                    Auditor::make()
                        ->event('compilation.clip.moved')
                        ->old(['compilation_id' => $livewire->getOwnerRecord()->getKey()])
                        ->new(['compilation_id' => (int) $data['compilation_id']])
                        ->on($livewire->getOwnerRecord())
                        ->save();
                } catch (Halt $e) {
                    throw $e;
                } catch (Exception $e) {
                    report($e);

                    Notification::make('unknown-error-while-moving')
                        ->title('Could not move Clip')
                        ->body('There was an error while moving clip, please contact IT if the issue persists')
                        ->danger()
                        ->send();

                    $this->halt();
                }
            })
            ->successNotificationTitle(__('admin/resources/compilations.relation_managers.clips.notifications.moved_to_compilation'));
    }

    public static function getDefaultName(): ?string
    {
        return 'moveToCompilation';
    }
}
