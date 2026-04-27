<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Actions\StoreReportAction;
use App\Enums\Filament\LucideIcon;
use App\Enums\Reports\ReportReason;
use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;

class ReportAction extends Action
{
    protected Model|Closure|null $reportableOverride = null;

    protected ?string $reportableAliasOverride = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(LucideIcon::Flag)
            ->color('danger')
            ->schema($this::getReportModalSchema())
            ->label(function (?Model $record): string {
                $target = $this->resolveReportable($record);

                if (! $target instanceof Model) {
                    return __('reports.modal.title', ['reportable' => 'Resource']);
                }

                return __('reports.modal.title', ['reportable' => $this->resolveModelAlias($target)]);
            });

        $this->action(function (array $data, ?Model $record, StoreReportAction $storeReportAction): void {
            $target = $this->resolveReportable($record);

            if (! $target instanceof Model) {
                return;
            }

            $report = $storeReportAction->execute($target, $data['reason'], auth()->user(), $data['description'] ?? null);

            Notification::make()
                ->title(__('reports.modal.success.title'))
                ->body(__('reports.modal.success.message').' '.__('reports.modal.success.report-id').$report->id)
                ->success()
                ->send();
        });

        $this->disabled(function (?Model $record): bool {
            $target = $this->resolveReportable($record);

            if (! $target || ! method_exists($target, 'reports')) {
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

    private static function getReportModalSchema(): array
    {
        return [
            Select::make('reason')
                ->label('reports.modal.inputs.reason.label')
                ->options(ReportReason::class)
                ->translateLabel()
                ->required(),

            Textarea::make('description')
                ->label('reports.modal.inputs.description.label')
                ->required(fn (Get $get): bool => $get('reason') === ReportReason::Other)
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
