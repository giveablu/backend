<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\SocialProvider;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;

trait InteractsWithSocialProviders
{
    protected function buildDriver(SocialProvider $provider, ?string $redirectUri, ?string $state = null): Provider
    {
        $driverName = $this->driverName($provider);

        $effectiveRedirect = $redirectUri;

        if ($provider === SocialProvider::X && $redirectUri && $state) {
            $effectiveRedirect = $this->mergeQueryParameters($redirectUri, ['state' => $state]);
        }

        if ($provider === SocialProvider::X && $effectiveRedirect) {
            config(['services.twitter.redirect' => $effectiveRedirect]);
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

        if ($effectiveRedirect && $provider !== SocialProvider::X && method_exists($driver, 'redirectUrl')) {
            $driver->redirectUrl($effectiveRedirect);
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

    private function mergeQueryParameters(string $url, array $parameters): string
    {
        $fragment = '';

        $hashPosition = strpos($url, '#');
        if ($hashPosition !== false) {
            $fragment = substr($url, $hashPosition);
            $url = substr($url, 0, $hashPosition);
        }

        $parts = parse_url($url);

        if ($parts === false) {
            $separator = str_contains($url, '?') ? '&' : '?';

            return $url . $separator . http_build_query($parameters) . $fragment;
        }

        $query = [];

        if (isset($parts['query']) && $parts['query'] !== '') {
            parse_str($parts['query'], $query);
        }

        foreach ($parameters as $key => $value) {
            if ($value === null) {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }

        $parts['query'] = http_build_query($query);

        $rebuilt = $this->unparseUrl($parts);

        return $rebuilt . $fragment;
    }

    private function unparseUrl(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        $userInfo = '';
        if (isset($parts['user'])) {
            $userInfo = $parts['user'];
        }

        if (isset($parts['pass'])) {
            $userInfo .= ':' . $parts['pass'];
        }

        if ($userInfo !== '') {
            $userInfo .= '@';
        }

        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

        return $scheme . $userInfo . $host . $port . $path . $query;
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
