<?php

declare(strict_types=1);

namespace App\Filament\Actions\Tables;

use App\Enums\Clips\ClipStatus;
use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

class UpdateClipStatusAction extends Action
{
    protected bool $moderation = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(LucideIcon::Info)
            ->label('filament/actions/tables.clips.update_clip_status.label')
            ->translateLabel()
            ->authorize(fn (Clip $record): bool => auth()->user()->can('update', $record))
            ->schema(fn (Clip $clip): array => [
                Select::make('status')
                    ->disableOptionWhen(fn (int $value): bool => ! $this->moderation
                        && in_array(ClipStatus::tryFrom($value), [ClipStatus::Unknown, ClipStatus::NeedApproval], true)
                    )
                    ->options(
                        collect(ClipStatus::cases())
                            ->filter(fn (ClipStatus $case): bool => $this->moderation
                                ? $case !== ClipStatus::Unknown || $clip->status === $case
                                : ! in_array($case, [ClipStatus::Unknown, ClipStatus::NeedApproval], true) || $clip->status === $case
                            )
                            ->mapWithKeys(fn ($case): array => [$case->value => $case->getLabel()])
                    )
                    ->native(false)
                    ->label('filament/actions/tables.clips.update_clip_status.form.status')
                    ->translateLabel()
                    ->required(),
            ])
            ->fillForm(fn (Clip $record): array => [
                'status' => $record->status,
            ])
            ->action(function (array $data, Model $record): void {
                $record->update(['status' => $data['status']]);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'updateClipStatus';
    }

    public function moderation(bool $moderation = true): static
    {
        $this->moderation = $moderation;

        return $this;
    }
}
