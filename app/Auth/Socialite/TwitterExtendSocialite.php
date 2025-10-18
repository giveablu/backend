<?php

namespace App\Auth\Socialite;

use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Twitter\Server as BaseTwitterServer;

class TwitterExtendSocialite
{
    public function handle(SocialiteWasCalled $event): void
    {
        $event->extendSocialite('twitter', TwitterProvider::class, BaseTwitterServer::class);
    }
}
