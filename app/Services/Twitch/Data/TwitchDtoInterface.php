<?php

namespace App\Services\Twitch\Data;

interface TwitchDtoInterface
{
    public static function from(array $clip);
    public static function fromArray(array $clips);
    public function toModel(?array $data);
}
