<?php

namespace App\Auth\Socialite;

use SocialiteProviders\Manager\SocialiteWasCalled;

class InstagramExtendSocialite
{
    public function handle(SocialiteWasCalled $event): void
    {
        $event->extendSocialite('instagram', InstagramProvider::class);
    }
}
