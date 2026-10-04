<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Actions\StoreReportAction;
use App\Enums\Filament\LucideIcon;
use App\Enums\Reports\ReportCategoryDetailsType;
use App\Models\ReportCategory;
use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ReportAction extends Action
{
    protected Model|Closure|null $reportableOverride = null;

    protected ?string $reportableAliasOverride = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(LucideIcon::Flag)
            ->color('danger')
            ->schema($this->getReportModalSchema())
            ->label(function (?Model $record): string {
                $target = $this->resolveReportable($record);

                if (! $target instanceof Model) {
                    return __('reports.modal.title', ['reportable' => 'Resource']);
                }

                return __('reports.modal.title', ['reportable' => $this->resolveModelAlias($target)]);
            });

        $this->action(function (array $data, ?Model $record, StoreReportAction $storeReportAction): void {
            $target = $this->resolveReportable($record);
            $category = ReportCategory::find($data['category_id']);

            if (! $target instanceof Model || ! $category instanceof ReportCategory) {
                return;
            }

            $report = $storeReportAction->execute($target, $category, auth()->user(), $data['description'] ?? null);

            Notification::make()
                ->title(__('reports.modal.success.title'))
                ->body(__('reports.modal.success.message').' '.__('reports.modal.success.report-id').$report->id)
                ->success()
                ->send();
        });

        $this->disabled(function (?Model $record): bool {
            $target = $this->resolveReportable($record);

            if (! $target instanceof Model || ! method_exists($target, 'reports')) {
                return true;
            }

            /** @var User|null $user */
            $user = Filament::auth()->user();

            if (! $user) {
                return true;
            }

            return $target->reports()
                ->withTrashed()
                ->where('user_id', $user->getKey())
                ->where('reportable_id', $target->getKey())
                ->where('reportable_type', $target->getMorphClass())
                ->exists();
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'report';
    }

    public function reportable(Model|Closure $reportable): static
    {
        $this->reportableOverride = $reportable;

        return $this;
    }

    public function reportableAlias(?string $alias = null): static
    {
        $this->reportableAliasOverride = $alias;

        return $this;
    }

    protected function resolveReportable(?Model $record): ?Model
    {
        if (! $record instanceof Model) {
            return null;
        }

        if ($this->reportableOverride instanceof Model) {
            return $this->reportableOverride;
        }

        if ($this->reportableOverride instanceof Closure) {
            return ($this->reportableOverride)($record);
        }

        return $record;
    }

    private function getReportModalSchema(): array
    {
        return [
            Select::make('category_id')
                ->label('reports.modal.inputs.reason.label')
                ->options(function (?Model $record): Collection {
                    $target = $this->resolveReportable($record);

                    return ReportCategory::query()
                        ->whereIsNote(false)
                        ->when($target, fn (Builder $query): Builder => $query->whereAppliesToReportableType($target->getMorphClass()))
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(fn (ReportCategory $c): array => [
                            $c->id => '<div class="font-medium text-gray-950 dark:text-white">'.e($c->name).'</div>'
                                .($c->summary
                                    ? '<div class="text-xs text-gray-600 dark:text-gray-400">'.e($c->summary).'</div>'
                                    : ''
                                ),
                        ]);
                })
                ->searchable()
                ->allowHtml()
                ->live()
                ->translateLabel()
                ->required(),

            Textarea::make('description')
                ->label('reports.modal.inputs.description.label')
                ->required(fn (Get $get): bool => ($get('category_id') ? ReportCategory::query()->find($get('category_id'))?->details_type : null) === ReportCategoryDetailsType::Required)
                ->disabled(fn (Get $get): bool => $get('category_id') === null || ReportCategory::query()->find($get('category_id'))?->details_type === ReportCategoryDetailsType::Disabled)
                ->maxLength(1000)
                ->translateLabel()
                ->rows(3),
        ];
    }

    private function resolveModelAlias(Model $target): string
    {
        if ($this->reportableAliasOverride) {
            return $this->reportableAliasOverride;
        }

        return method_exists($target, 'getReportableAlias')
            ? $target->getReportableAlias()
            : class_basename($target);
    }
}
