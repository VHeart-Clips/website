<?php

namespace App\Services\Twitch\Data;

interface TwitchDtoInterface
{
    public static function from(array $data);
    public static function fromArray(array $dataList);
    public function toModel(?array $data);
}
