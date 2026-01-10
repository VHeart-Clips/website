<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Pages;

use App\Filament\Resources\Clips\ClipResource;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditClip extends EditRecord
{
    protected static string $resource = ClipResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Edit :label by :broadcaster', [
            'label' => $this->getRecordTitle(),
            'broadcaster' => $this->getRecord()->broadcaster?->name,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Claim')
                ->icon(Heroicon::LockClosed)
                ->disabled(),
            Action::make('download')
                ->color('info')
                ->icon(Heroicon::ArrowDownTray)
                ->disabled(),

            ActionGroup::make([
                Action::make('open_twitch')
                    ->label('View On Twitch')
                    ->icon(Heroicon::Link)
                    ->url(function (Clip $clip) {
                        return $clip->url;
                    })
                    ->openUrlInNewTab(),

                DeleteAction::make(),
            ]),
        ];
    }
}
