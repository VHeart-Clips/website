<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Pages;

use App\Filament\AdminPanel\Resources\Compilations\CompilationResource;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ViewCompilation extends ViewRecord
{
    protected static string $resource = CompilationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make()
                ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                ->authorize('comment')
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),
            EditAction::make(),
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ]),
        ];
    }
}
