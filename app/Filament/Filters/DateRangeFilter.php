<?php

declare(strict_types=1);

namespace App\Filament\Filters;

use App\Enums\Filament\LucideIcon;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DateRangeFilter extends Filter
{
    protected bool $hasPresets = false;

    protected ?array $customPresets = null;

    protected string|Htmlable|null $indicatorLabel = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            Fieldset::make($this->getName().'_fieldset')
                ->label(fn (): Htmlable|string => $this->getLabel())
                ->columnSpanFull()
                ->schema(array_filter([
                    Select::make('presets')
                        ->hidden(fn (): bool => ! $this->hasPresets)
                        ->label('filament/filters/date_range.presets.label')
                        ->translateLabel()
                        ->dehydrated(false)
                        ->options(fn (): array => collect($this->resolvePresets())
                            ->mapWithKeys(fn (array $preset, string $key): array => [$key => $preset['label']])
                            ->toArray())
                        ->live()
                        ->afterStateUpdated(function (string $state, Set $set): void {
                            if (blank($state)) {
                                return;
                            }

                            $presets = $this->resolvePresets();

                            if (! isset($presets[$state])) {
                                return;
                            }

                            $preset = $presets[$state];

                            $from = value($preset['from'] ?? null);
                            $to = value($preset['to'] ?? null);

                            $set('from', $from instanceof CarbonInterface ? $from->toDateString() : $from);
                            $set('to', $to instanceof CarbonInterface ? $to->toDateString() : $to);
                            $set('presets', null);
                        })
                        ->columnSpanFull(),
                    DatePicker::make('from')
                        ->label('filament/filters/date_range.form.from')
                        ->translateLabel()
                        ->suffixAction(
                            Action::make($this->getName().'_clear_from')
                                ->label('filament/filters/date_range.actions.clear_from')
                                ->translateLabel()
                                ->iconButton()
                                ->icon(LucideIcon::X)
                                ->color('gray')
                                ->action(function (Set $set): void {
                                    $set('from', null);
                                }),
                        ),
                    DatePicker::make('to')
                        ->label('filament/filters/date_range.form.to')
                        ->translateLabel()
                        ->suffixAction(
                            Action::make($this->getName().'_clear_to')
                                ->label('filament/filters/date_range.actions.clear_to')
                                ->translateLabel()
                                ->iconButton()
                                ->icon(LucideIcon::X)
                                ->color('gray')
                                ->action(function (Set $set): void {
                                    $set('to', null);
                                }),
                        ),
                ]))
                ->columns(2),
        ])
            ->columns(2)
            ->columnSpanFull()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(
                    $data['from'],
                    fn (Builder $query, string $date): Builder => $query->whereDate($this->getName(), '>=', $date),
                )
                ->when(
                    $data['to'],
                    fn (Builder $query, string $date): Builder => $query->whereDate($this->getName(), '<=', $date),
                ))
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                if ($data['from'] ?? null) {
                    $indicators[$this->getName().'_range_from'] = __('filament/filters/date_range.indicators.from', [
                        'name' => $this->indicatorLabel ?? $this->getLabel(),
                        'value' => Carbon::parse($data['from'])->toFormattedDateString(),
                    ]);
                }
                if ($data['to'] ?? null) {
                    $indicators[$this->getName().'_range_to'] = __('filament/filters/date_range.indicators.to', [
                        'name' => $this->indicatorLabel ?? $this->getLabel(),
                        'value' => Carbon::parse($data['to'])->toFormattedDateString(),
                    ]);
                }

                return $indicators;
            });
    }

    /**
     * Helper to make preset options
     *
     * @param  Closure(): (DateTimeInterface|string|null)  $fromResolver
     * @param  Closure(): (DateTimeInterface|string|null)  $toResolver
     * @return array{label: string|Htmlable, from: Closure, to: Closure}
     */
    public static function makePreset(string|Htmlable $label, Closure $fromResolver, Closure $toResolver): array
    {
        return [
            'label' => $label,
            'from' => $fromResolver,
            'to' => $toResolver,
        ];
    }

    public function indicatorLabel(string|Htmlable $label): static
    {
        $this->indicatorLabel = $label;

        return $this;
    }

    public function withPresets(bool $condition = true): static
    {
        $this->hasPresets = $condition;

        return $this;
    }

    public function presets(array $presets): static
    {
        $this->customPresets = $presets;

        return $this;
    }

    public function resolvePresets(): array
    {
        return $this->customPresets ?? [
            'today' => [
                'label' => __('filament/filters/date_range.presets.default_options.today'),
                'from' => fn () => now()->startOfDay(),
                'to' => fn (): null => null,
            ],
            'last_7_days' => [
                'label' => __('filament/filters/date_range.presets.default_options.last_7_days'),
                'from' => fn () => now()->subDays(6)->startOfDay(),
                'to' => fn (): null => null,
            ],
            'last_30_days' => [
                'label' => __('filament/filters/date_range.presets.default_options.last_30_days'),
                'from' => fn () => now()->subDays(29)->startOfDay(),
                'to' => fn (): null => null,
            ],
            'last_90_days' => [
                'label' => __('filament/filters/date_range.presets.default_options.last_90_days'),
                'from' => fn () => now()->subDays(89)->startOfDay(),
                'to' => fn (): null => null,
            ],
            'this_month' => [
                'label' => __('filament/filters/date_range.presets.default_options.this_month'),
                'from' => fn () => now()->startOfMonth(),
                'to' => fn (): null => null,
            ],
            'last_month' => [
                'label' => __('filament/filters/date_range.presets.default_options.last_month'),
                'from' => fn () => now()->subMonth()->startOfMonth(),
                'to' => fn () => now()->subMonth()->endOfMonth(),
            ],
        ];
    }
}
