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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\View\View;
use Livewire\Component;
use stdClass;

class GenerateTopOverlayAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

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
                'from' => now()->startOfWeek(Carbon::MONDAY)->previous(Carbon::THURSDAY),
                'to' => now()->startOfWeek(Carbon::MONDAY)->next(Carbon::THURSDAY),
            ])->schema([

                Grid::make()
                    ->schema([
                        DatePicker::make('from')
                            ->hintIcon(LucideIcon::CircleQuestionMark, 'The Week for Compilations starts at Thursday 00:00 from the previews Week of the Compilations')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('top-overlay-updated', ...$this->buildOverlayState($get))),
                        DatePicker::make('to')
                            ->hintIcon(LucideIcon::CircleQuestionMark, 'The Week for Compilations ends at Thursday 00:00 from the current Week of the Compilations')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('top-overlay-updated', ...$this->buildOverlayState($get))),
                    ]),
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
                        'users' => $this->getUsers(TopOverlayType::Votes, now()->startOfWeek(Carbon::MONDAY)->previous(Carbon::THURSDAY), now()->startOfWeek(Carbon::MONDAY)->next(Carbon::THURSDAY)),
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

                return (object) $data->toArray();
            })->values();

        return $leaderBoardUsers->toArray();
    }
}
