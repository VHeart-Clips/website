<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\RemovalRequests\Schemas;

use App\Enums\Broadcaster\RemovalRequestStatus;
use App\Enums\Clips\CompilationStatus;
use App\Filament\Resources\Clips\ClipSelect;
use App\Models\Broadcaster\RemovalRequest;
use App\Models\Clip\Compilation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Contracts\Database\Query\Builder;

class RemovalRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('compilation_id')
                    ->label('dashboard/resources/removal-requests.form.compilation_id')
                    ->translateLabel()
                    ->visibleOn(Operation::Create)
                    ->options(fn () => Compilation::query()
                        ->whereHas('clips')
                        ->whereIn('status', CompilationStatus::getVoteDisabledCases())
                        ->pluck('title', 'id')
                    )
                    ->dehydrated(false)
                    ->searchable()
                    ->live(),

                ClipSelect::make('clip_id')
                    ->disabledOn(Operation::Edit)
                    ->modifyClipQueryUsing(fn ($query, ?string $search, Get $get) => filled($compilationId = $get('compilation_id'))
                        ? $query->whereHas('compilations', fn (Builder $q) => $q->whereIn('status', CompilationStatus::getVoteDisabledCases())->where('compilations.id', $compilationId))
                        : $query->whereHas('compilations', fn (Builder $q) => $q->whereIn('status', CompilationStatus::getVoteDisabledCases()))
                    )
                    ->disableOptionWhen(fn (string|int $value) => RemovalRequest::query()
                        ->whereNot('status', RemovalRequestStatus::Rejected)
                        ->where('clip_id', $value)
                        ->exists()
                    )
                    ->preload()
                    ->required(),
                Textarea::make('details')
                    ->label('dashboard/resources/removal-requests.form.details')
                    ->translateLabel()
                    ->maxLength(50 * 1000)
                    ->minLength(10)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
}
