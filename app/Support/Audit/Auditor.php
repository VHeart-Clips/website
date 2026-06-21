<?php

declare(strict_types=1);

namespace App\Support\Audit;

use App\Models\Audit;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use UnitEnum;

class Auditor
{
    protected ?Model $causer = null;

    protected ?Model $auditable = null;

    protected string $event = 'custom_event';

    protected ?array $old = null;

    protected ?array $new = null;

    protected ?array $tags = null;

    // we want this by default, only disable on security related stuff
    protected bool $anonymize = true;

    protected ?string $requestId = null;

    public static function make(): static
    {
        $instance = new static();
        $instance->requestId = context('request_id');

        if (auth()->check()) {
            $instance->causer = auth()->user();
        }

        return $instance;
    }

    public static function resolveSimpleDifferences(?array $old = null, ?array $new = null): array
    {
        $originalCollection = collect($old);
        $updatedCollection = collect($new);

        $changed = $originalCollection->keys()
            ->merge($updatedCollection->keys())
            ->unique()
            ->filter(fn (mixed $key): bool => $originalCollection->get($key) !== $updatedCollection->get($key));

        return [
            $changed->mapWithKeys(fn (mixed $key): array => [$key => $originalCollection->get($key)])->toArray(),
            $changed->mapWithKeys(fn (mixed $key): array => [$key => $updatedCollection->get($key)])->toArray(),
        ];
    }

    public function causer(?Model $causer): static
    {
        $this->causer = $causer;

        return $this;
    }

    public function on(?Model $auditable): static
    {
        $this->auditable = $auditable;

        return $this;
    }

    public function event(string|UnitEnum|BackedEnum $event): static
    {
        $this->event = match (true) {
            $event instanceof BackedEnum => (string) $event->value,
            $event instanceof UnitEnum => $event->name,

            default => $event,
        };

        return $this;
    }

    public function old(array $old): static
    {
        $this->old = $old;

        return $this;
    }

    public function new(array $new): static
    {
        $this->new = $new;

        return $this;
    }

    public function tags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Masks the IP and Hashes the User agent
     * we want this by default, only disable on security related stuff
     */
    public function anonymize(bool $anonymize = true): static
    {
        $this->anonymize = $anonymize;

        return $this;
    }

    public function save(): Audit
    {
        $ip = Request::ip();
        $ua = Request::userAgent();

        if ($this->anonymize && $this->causer) {
            $ip = $this->maskIp($ip);
            $ua = $ua ? hash('sha256', $ua) : null;
        } elseif (! $this->causer instanceof Model) {
            // We act on behalf of the System, no need for ip or ua
            $ip = null;
            $ua = null;
        }

        return Audit::create([
            'causer_type' => $this->causer?->getMorphClass(),
            'causer_id' => $this->causer?->getKey(),
            'auditable_type' => $this->auditable?->getMorphClass(),
            'auditable_id' => $this->auditable?->getKey(),
            'event' => $this->event,
            'old' => $this->old,
            'new' => $this->new,
            'tags' => $this->tags,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'request_id' => $this->requestId,
        ]);
    }

    /**
     * Masks an IPv4 to /16 and an IPv6 to /64
     *
     * also kinda future proof as we simply wipe the second half of anything that is considered an ip by php
     */
    protected function maskIp(?string $ip): ?string
    {
        $packed = inet_pton($ip);
        if (! $ip || ! $packed) {
            return null;
        }

        $length = mb_strlen($packed, '8bit'); // would use strlen but pint would cry otherwise
        $half = $length / 2;

        for ($i = $half; $i < $length; $i++) {
            $packed[$i] = "\0";
        }

        return inet_ntop($packed);
    }
}
