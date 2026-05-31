<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Contracts\ExternalProxyable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

enum ExternalContentProxyType: string
{
    case TwitchUser = 'user';

    /**
     * @return class-string<Model&ExternalProxyable>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::TwitchUser => User::class,
        };
    }

    public function getResource(string $identifier): ?string
    {
        $size = null;
        $dbId = $identifier;
        $modelClass = $this->modelClass();

        if ($modelClass::supportsProxyDynamicSize() && Str::contains($identifier, '-')) {
            $size = Str::afterLast($identifier, '-');
            $dbId = Str::beforeLast($identifier, '-');
        }

        $model = $modelClass::where($modelClass::getProxyIdentifierColumn(), $dbId)->firstOrFail();

        $url = $model->getAttribute($modelClass::getProxyUrlColumn());

        if (! $url) {
            return null;
        }

        if ($size && $url) {
            return str_replace('{width}x{height}', $size, $url);
        }

        return $url;
    }
}
