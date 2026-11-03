<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Pages;

use App\Filament\AdminPanel\Resources\Compilations\CompilationResource;
use App\Models\Clip\Compilation;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class EditCompilation extends EditRecord
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
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Compilation
    {
        /** @var Compilation $compilation */
        $compilation = parent::handleRecordUpdate($record, $data);

        if (
            $compilation->wasChanged(['status'])
            && ($compilation->status->isPublic() || $compilation->status->isScheduled())
        ) {
            return $compilation->scheduleExport();
        }

        return $compilation;
    }
}
