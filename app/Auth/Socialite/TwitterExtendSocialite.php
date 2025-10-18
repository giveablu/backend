<?php

namespace App\Auth\Socialite;

use SocialiteProviders\Manager\SocialiteWasCalled;

class TwitterExtendSocialite
{
    public function handle(SocialiteWasCalled $event): void
    {
        $event->extendSocialite('twitter', TwitterProvider::class);
    }
}
