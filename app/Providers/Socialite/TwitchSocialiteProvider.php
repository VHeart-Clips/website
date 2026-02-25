<?php

declare(strict_types=1);

namespace App\Providers\Socialite;

use SocialiteProviders\Twitch\Provider;

class TwitchSocialiteProvider extends Provider
{
    protected $scopes = [];
}
