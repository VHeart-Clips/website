<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests\RelationManagers;

use App\Filament\AdminPanel\Resources\Clips\RelationManagers\CompilationsRelationManager;
use App\Models\Broadcaster\RemovalRequest;
use Illuminate\Database\Eloquent\Model;

class RemovalRequestCompilationsRelationManager extends CompilationsRelationManager
{
    public function getOwnerRecord(): Model
    {
        /** @var RemovalRequest $record */
        $record = parent::getOwnerRecord();

        return $record->clip;
    }
}
