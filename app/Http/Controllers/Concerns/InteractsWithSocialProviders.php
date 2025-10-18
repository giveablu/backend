<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\SocialProvider;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;

trait InteractsWithSocialProviders
{
    protected function buildDriver(SocialProvider $provider, ?string $redirectUri): Provider
    {
        $driverName = $this->driverName($provider);

        if ($provider === SocialProvider::X && $redirectUri) {
            config(['services.twitter.redirect' => $redirectUri]);
        }

        $driver = Socialite::driver($driverName);

        if ($provider === SocialProvider::X) {
            if (method_exists($driver, 'usingStateManager')) {
                $driver->usingStateManager($this->stateManager);
            }
        }

        $graphVersion = match ($provider) {
            SocialProvider::Facebook => config('services.facebook.graph_version'),
            SocialProvider::Instagram => config('services.instagram.graph_version'),
            default => null,
        };

        if (is_string($graphVersion) && $graphVersion !== '' && method_exists($driver, 'usingGraphVersion')) {
            $driver->usingGraphVersion($graphVersion);
        }

        if ($redirectUri && $provider !== SocialProvider::X && method_exists($driver, 'redirectUrl')) {
            $driver->redirectUrl($redirectUri);
        }

        $scopes = $this->scopesForProvider($provider);
        if ($scopes) {
            $driver->scopes($scopes);
        }

        $with = $this->parametersForProvider($provider);
        if ($with) {
            $driver->with($with);
        }

        return $driver->stateless();
    }

    protected function resolveProviderForRole(string $provider, ?string $role): SocialProvider
    {
        $enum = SocialProvider::fromService($provider);

        if (! $role) {
            return $enum;
        }

        $allowed = match ($role) {
            'receiver' => [SocialProvider::Facebook, SocialProvider::Instagram, SocialProvider::X],
            'donor' => [SocialProvider::Facebook, SocialProvider::Instagram, SocialProvider::X, SocialProvider::Google],
            default => [],
        };

        if (! in_array($enum, $allowed, true)) {
            abort(404);
        }

        return $enum;
    }

    private function driverName(SocialProvider $provider): string
    {
        return match ($provider) {
            SocialProvider::Facebook => 'facebook',
            SocialProvider::Instagram => 'instagram',
            SocialProvider::X => 'twitter',
            SocialProvider::Google => 'google',
        };
    }

    private function scopesForProvider(SocialProvider $provider): array
    {
        return match ($provider) {
            SocialProvider::Facebook => ['public_profile', 'email'],
            SocialProvider::Instagram => ['user_profile'],
            SocialProvider::X => ['tweet.read', 'users.read'],
            SocialProvider::Google => ['openid', 'profile', 'email'],
        };
    }

    private function parametersForProvider(SocialProvider $provider): array
    {
        return match ($provider) {
            SocialProvider::Instagram => ['response_type' => 'code'],
            SocialProvider::X => ['include_email' => 'true'],
            default => [],
        };
    }
}
