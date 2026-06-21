<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Enums\Filament\LucideIcon;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ResourceLinkAction extends Action
{
    protected string|Closure $via;

    protected string $preferredPage = 'view';

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(LucideIcon::ExternalLink)
            ->url(fn (Model $record): ?string => $this->resolveUrl($record))
            ->visible(fn (Model $record): bool => $this->resolveUrl($record) !== null)
            ->label('View');
    }

    public static function getDefaultName(): ?string
    {
        return 'resourceLink';
    }

    public function relationship(string|Closure $relationship): static
    {
        $this->via = $relationship;

        return $this;
    }

    public function preferPage(string $page): static
    {
        $this->preferredPage = $page;

        return $this;
    }

    protected function resolveUrl(Model $record): ?string
    {
        $related = value($this->via, $record)
                |> (static fn (string $rel): array => explode('.', $rel))
                |> (static fn (array $segments) => array_reduce($segments, static fn (?Model $carry, string $segment) => $carry instanceof Model ? $carry->{$segment} : null, $record));

        if (! $related instanceof Model) {
            return null;
        }

        if (Filament::getModelResource($related) === null) {
            return null;
        }

        $chain = $this->preferredPage === 'index' ? ['index'] : [$this->preferredPage, $this->preferredPage === 'view' ? 'edit' : 'view'];

        foreach ($chain as $page) {
            $ability = match ($page) {
                'edit' => 'update',
                'index' => 'viewAny',
                default => 'view',
            };

            $can = $ability === 'viewAny'
                ? auth()->user()->can($ability, $related::class)
                : auth()->user()->can($ability, $related);

            if (! $can) {
                continue;
            }

            try {
                return Filament::getResourceUrl($related, $page);
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return null;
    }
}
