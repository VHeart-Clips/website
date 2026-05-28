<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Audit;
use App\Support\Audit\Auditor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use JsonException;

/**
 * Set up a model for Auditing
 *
 * @property string[] $auditExclude Attributes/columns to exclude from the audit log. Hidden attributes are excluded by default.
 * @property string[] $auditExcludeEvents Events to ignore (e.g. ['created', 'updated', 'deleted', 'restored', 'forceDeleted']).
 * @property bool $anonymizeAudit Enables IP and User agent anonymization for the model (defaults to true)
 * @property string[] $auditTags Array of custom tags to attach to the generated audit logs.
 *
 * @method array getExtraAuditData() Implement this to append custom data not directly on the model to the audit state.
 *
 * @mixin Model
 * @mixin SoftDeletes
 */
trait Auditable
{
    /** @internal Used by Audit Logs, do not use directly. */
    protected array $originalExtraAuditState = [];

    /** @internal Used by Audit Logs, do not use directly. */
    protected bool $deferAudit = false;

    /** @internal Used by Audit Logs, do not use directly. */
    protected array $stashedStandardChanges = [];

    /** @internal Used by Audit Logs, do not use directly. */
    protected array $stashedStandardOriginals = [];

    public function __destruct()
    {
        if ($this->deferAudit) {
            Log::error('Auditable model ['.self::class.'] did not call commitExtraAudit() in deferred mode.');
        }
    }

    /** @internal Used by Audit Logs, do not use directly. */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            if (in_array('created', $model->getIgnoredAuditEvents(), true)) {
                return;
            }

            if (! $model->deferAudit) {
                $model->recordAudit('created');
            }
        });

        static::updating(function (Model $model): void {
            $event = $model->isRestoringAuditEvent() ? 'restored' : 'updated';
            if (in_array($event, $model->getIgnoredAuditEvents(), true)) {
                return;
            }

            if ($model->originalExtraAuditState === []) {
                $model->cacheExtraAuditState();
            }
        });

        static::updated(function (Model $model): void {
            $event = $model->isRestoringAuditEvent() ? 'restored' : 'updated';
            if (in_array($event, $model->getIgnoredAuditEvents(), true)) {
                return;
            }

            if ($model->deferAudit) {
                $model->stashedStandardChanges = $model->getChanges();
                $model->stashedStandardOriginals = $model->getRawOriginal();
            } else {
                $model->recordAudit($event);
            }
        });

        static::deleted(function (Model $model): void {
            $event = (in_array(SoftDeletes::class, class_uses_recursive(static::class), true) && $model->isForceDeleting())
                ? 'forceDeleted'
                : 'deleted';

            if (in_array($event, $model->getIgnoredAuditEvents(), true)) {
                return;
            }

            if (! $model->deferAudit) {
                $model->recordAudit($event);
            }
        });
    }

    /**
     * Allows you to manually defer Audit storing to add extra data that is not on the model itself.
     *
     * Requires commitExtraAudit to be called when done to store the audit log, will cause a exception otherwise.
     */
    public function prepareExtraAudit(): void
    {
        $this->deferAudit = true;

        if ($this->originalExtraAuditState === []) {
            $this->cacheExtraAuditState();
        }
    }

    /**
     * If finished, call this to commit the Audit log
     */
    public function commitExtraAudit(string $eventName = 'updated'): void
    {
        $this->deferAudit = false;
        $this->recordAudit($eventName);

        $this->originalExtraAuditState = [];
        $this->stashedStandardChanges = [];
        $this->stashedStandardOriginals = [];
    }

    /**
     * Allows you to add extra values to the Audit log data which is not on the Model itself.
     */
    public function withExtraAudit(callable $callback, string $eventName = 'updated'): mixed
    {
        $this->prepareExtraAudit();

        $result = $callback($this);

        $this->commitExtraAudit($eventName);

        return $result;
    }

    /**
     * MorphMany Relationship to Audit log entries
     *
     * @return MorphMany<Audit, $this>
     */
    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function cacheExtraAuditState(): void
    {
        if (method_exists($this, 'getExtraAuditData')) {
            $this->originalExtraAuditState = $this->getExtraAuditData();
        }
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function isRestoringAuditEvent(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::class), true)
            && ($this->wasChanged($this->getDeletedAtColumn()) || $this->isDirty($this->getDeletedAtColumn()))
            && $this->{$this->getDeletedAtColumn()} === null;
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function getIgnoredAuditEvents(): array
    {
        return $this->auditExcludeEvents ?? [];
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function getExcludedAuditKeys(): array
    {
        return array_flip(array_merge(
            $this->getHidden(),
            $this->auditExclude ?? [],
            [$this->getUpdatedAtColumn()]
        ));
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function recordAudit(string $event): void
    {
        $old = [];
        $new = [];
        $excludedKeys = $this->getExcludedAuditKeys();

        if (in_array($event, ['updated', 'restored'], true)) {
            $this->resolveUpdateDifferences($old, $new, $excludedKeys);

            if ($event === 'restored' && method_exists($this, 'getDeletedAtColumn')) {
                $deletedAtCol = $this->getDeletedAtColumn();
                unset($old[$deletedAtCol], $new[$deletedAtCol]);
            }

            if ($event === 'updated' && collect($new)->diff($old)->isEmpty()) {
                return;
            }
        } elseif (in_array($event, ['created', 'deleted', 'forceDeleted'], true)) {
            if ($event === 'created') {
                $new = $this->resolveCompleteAuditState($excludedKeys);
            } elseif ($event === 'forceDeleted') {
                $old = $this->resolveCompleteAuditState($excludedKeys);
            }
        }

        $this->persistAuditLog($event, $old, $new);
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function resolveUpdateDifferences(array &$old, array &$new, array $excludedKeys): void
    {
        $changes = $this->stashedStandardChanges ?: $this->getChanges();
        $filteredChanges = array_diff_key($changes, $excludedKeys);
        $rawAttributes = $this->getAttributes();

        foreach (array_keys($filteredChanges) as $key) {
            $rawOriginal = $this->stashedStandardOriginals[$key] ?? $this->getRawOriginal($key);

            $originalValue = $this->decodeRawValueForAuditing($rawOriginal);
            $newValue = $this->decodeRawValueForAuditing($rawAttributes[$key] ?? null);

            if ($originalValue !== $newValue) {
                $old[$key] = $originalValue;
                $new[$key] = $newValue;
            }
        }

        if (method_exists($this, 'getExtraAuditData')) {
            $newExtra = array_diff_key($this->getExtraAuditData(), $excludedKeys);
            $oldExtra = array_diff_key($this->originalExtraAuditState, $excludedKeys);

            foreach ($newExtra as $key => $value) {
                $oldValue = $oldExtra[$key] ?? null;
                if ($oldValue !== $value) {
                    $old[$key] = $oldValue;
                    $new[$key] = $value;
                }
            }

            foreach ($oldExtra as $key => $oldValue) {
                if (! array_key_exists($key, $newExtra)) {
                    $old[$key] = $oldValue;
                    $new[$key] = null;
                }
            }
        }
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function resolveCompleteAuditState(array $excludedKeys): array
    {
        $filteredAttributes = array_diff_key($this->getAttributes(), $excludedKeys);

        $processedState = array_map(
            fn ($value) => $this->decodeRawValueForAuditing($value),
            $filteredAttributes
        );

        if (method_exists($this, 'getExtraAuditData')) {
            $filteredExtra = array_diff_key($this->getExtraAuditData(), $excludedKeys);
            $processedState = array_merge($processedState, $filteredExtra);
        }

        return $processedState;
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function persistAuditLog(string $event, array $old, array $new): void
    {
        $auditLog = Auditor::make()
            ->on($this)
            ->event($event)
            ->anonymize($this->anonymizeAudit ?? true);

        if ($old !== []) {
            $auditLog->old($old);
        }

        if ($new !== []) {
            $auditLog->new($new);
        }

        if (! empty($this->auditTags)) {
            $auditLog->tags($this->auditTags);
        }

        $auditLog->save();
    }

    /** @internal Used by Audit Logs, do not use directly. */
    protected function decodeRawValueForAuditing(mixed $value): mixed
    {
        if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            try {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return $value;
            }
        }

        return $value;
    }
}
