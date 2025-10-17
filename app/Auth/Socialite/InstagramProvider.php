<?php

namespace App\Auth\Socialite;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Instagram\Provider as BaseInstagramProvider;

class InstagramProvider extends BaseInstagramProvider
{
    protected ?string $graphVersion = null;

    public function usingGraphVersion(string $version): static
    {
        $version = trim($version);
        $this->graphVersion = $version !== '' ? $version : null;

        return $this;
    }

    protected function graphEndpoint(string $path): string
    {
        $base = 'https://graph.instagram.com';

        $version = $this->graphVersion ?? config('services.instagram.graph_version');
        if (is_string($version) && $version !== '') {
            $version = trim($version, '/');
            $path = ltrim($path, '/');

            return $base.'/'.($version !== '' ? $version.'/' : '').$path;
        }

        return $base.'/'.ltrim($path, '/');
    }

    protected function getUserByToken($token)
    {
        $queryParameters = [
            'access_token' => $token,
            'fields' => implode(',', $this->fields),
        ];

        if (! empty($this->clientSecret)) {
            $queryParameters['appsecret_proof'] = hash_hmac('sha256', $token, $this->clientSecret);
        }

        $response = $this->getHttpClient()->get($this->graphEndpoint('me'), [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            RequestOptions::QUERY => $queryParameters,
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
