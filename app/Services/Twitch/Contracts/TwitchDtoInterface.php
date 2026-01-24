<?php

namespace App\Services\Twitch\Contracts;

interface TwitchDtoInterface
{
    public static function from(array $data);
    public static function fromArray(array $dataList);
    public function toModel(?array $data);
}
