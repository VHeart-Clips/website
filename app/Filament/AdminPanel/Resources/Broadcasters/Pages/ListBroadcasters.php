<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\Pages;

use App\Filament\AdminPanel\Resources\Broadcasters\BroadcasterResource;
use App\Models\Broadcaster\Broadcaster;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBroadcasters extends ListRecords
{
    protected static string $resource = BroadcasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, string $model): Broadcaster {
                    $existing = $model::withTrashed()
                        ->where('id', $data['id'])
                        ->first();

                    if ($existing?->trashed()) {
                        if (! auth()->user()->can('restore', $existing)) {
                            Notification::make()
                                ->title('You are not authorized to restore this broadcaster.')
                                ->danger()
                                ->send();

                            $this->halt();
                        }

                        $existing->restore();

                        return $existing;
                    }

                    return $model::create($data);
                })
                ->after(fn (Broadcaster $record, self $livewire) => $livewire->redirect(
                    BroadcasterResource::getUrl('view', ['record' => $record])
                )),
        ];
    }
}
