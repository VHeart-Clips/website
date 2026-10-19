<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Actions\Management;

use App\Enums\Filament\LucideIcon;
use App\Enums\Filament\TopOverlayType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\View\View;
use Livewire\Component;
use stdClass;

class GenerateTopOverlayAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $startWeekDate = now()->startOfWeek(Carbon::MONDAY)->previous(Carbon::THURSDAY);
        $endWeekDate = $startWeekDate->next(Carbon::THURSDAY);

        $this
            ->label('Generate Top Overlay')
            ->icon(LucideIcon::Image)
            ->color('info')
            ->modalHeading('Generate Top Overlay')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitActionLabel('Close')
            ->modalCancelAction(false)
            ->fillForm(fn (): array => [
                'type' => TopOverlayType::Votes,
                'show_background' => true,
                'from' => $startWeekDate,
                'to' => $endWeekDate,
            ])->schema([
                FusedGroup::make()
                    ->schema([
                        DatePicker::make('from')
                            ->native(false)
                            ->closeOnDateSelection(),
                        DatePicker::make('to')
                            ->native(false)
                            ->closeOnDateSelection(),
                    ])->columns(2)
                    ->afterLabel(Schema::end([
                        Action::make('this_year')
                            ->label('This Year')
                            ->action(function (Set $set) use ($startWeekDate, $endWeekDate): void {
                                $set('from', $startWeekDate->startOfYear());
                                $set('to', $endWeekDate->endOfYear(), false, true);
                            }),
                        Action::make('this_week')
                            ->label('This Week')
                            ->action(function (Set $set) use ($startWeekDate, $endWeekDate): void {
                                $set('from', $startWeekDate);
                                $set('to', $endWeekDate, false, true);
                            }),
                        Icon::make(LucideIcon::CircleQuestionMark)->tooltip('The Week For a Compilation goes from last Week Thursday to currents Week Thursday at 00:00'),
                    ])->gap())
                    ->label('Range')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('top-overlay-updated', ...$this->buildOverlayState($get))),
                Select::make('type')
                    ->label('Type')
                    ->translateLabel()
                    ->options(TopOverlayType::class)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('top-overlay-updated', ...$this->buildOverlayState($get))),
                Toggle::make('show_background')
                    ->label('Show Background')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('top-overlay-updated', ...$this->buildOverlayState($get))),
            ])
            ->modalContentFooter(fn (): View => view(
                'filament.actions.generate-top-overlay',
                [
                    'initialState' => [
                        'type' => TopOverlayType::Votes,
                        'users' => $this->getUsers(TopOverlayType::Votes, $startWeekDate, $endWeekDate),
                        'show_background' => true,
                    ],
                    'identifier' => TopOverlayType::Votes,
                ]
            ))
            ->action(null);
    }

    public static function getDefaultName(): ?string
    {
        return 'generateTopOverlay';
    }

    private function buildOverlayState(Get $get): array
    {
        return [[
            'type' => $get('type'),
            'show_background' => $get('show_background'),
            'users' => $this->getUsers($get('type'), Carbon::parse($get('from')), Carbon::parse($get('to'))),
        ]];
    }

    private function getUsers(TopOverlayType $type, CarbonInterface $start, CarbonInterface $end): array
    {
        $queryClass = $type->getQueryClass();
        $leaderBoardUsers = new $queryClass()->handle($start, $end, 5);
        $users = User::whereIn('id', $leaderBoardUsers->pluck('id'))->get(['id', 'name', 'avatar_url']);

        $leaderBoardUsers = $leaderBoardUsers->reject(fn (array $user): bool => ! $users->contains('id', $user['id']))
            ->map(function (array $user) use ($users): stdClass {
                $data = $users->first(fn (User $item): bool => $item->id === $user['id']);

                $data->count = $user['count'];
                $data->anonym = false; // TODO:need to be replaced later with the user setting if we can show the user or not

                return (object) $data->toArray();
            })->values();

        return $leaderBoardUsers->toArray();
    }
}
