<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Pages;

use App\Filament\AdminPanel\Resources\Users\Actions\UpdateUserAction;
use App\Filament\AdminPanel\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UpdateUserAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
