<?php

namespace App\Services\SocialAuth;

use App\Enums\SocialProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Str;
use League\OAuth1\Client\Credentials\TemporaryCredentials;

class SocialAuthStateManager
{
    public function __construct(private CacheRepository $cache)
    {
    }

    public function generateState(SocialProvider $provider, string $intent, array $payload = []): string
    {
        $state = Str::uuid()->toString();

        $this->cache->put(
            $this->key($state),
            array_merge($payload, [
                'provider' => $provider->value,
                'intent' => $intent,
            ]),
            now()->addMinutes(10)
        );

        return $state;
    }

    public function resolve(string $state): ?array
    {
        return $this->cache->get($this->key($state));
    }

    public function forget(string $state): void
    {
        $this->cache->forget($this->key($state));
    }

    public function storeOauth1Credentials(string $state, TemporaryCredentials $credentials): void
    {
        $this->cache->put(
            $this->oauthKey($state),
            [
                'identifier' => $credentials->getIdentifier(),
                'secret' => $credentials->getSecret(),
            ],
            now()->addMinutes(10)
        );
    }

    public function pullOauth1Credentials(string $state): ?TemporaryCredentials
    {
        $payload = $this->cache->pull($this->oauthKey($state));

        if (! $payload || empty($payload['identifier']) || empty($payload['secret'])) {
            return null;
        }

        $credentials = new TemporaryCredentials();
        $credentials->setIdentifier($payload['identifier']);
        $credentials->setSecret($payload['secret']);

        return $credentials;
    }

    private function key(string $state): string
    {
        return 'social-auth:state:' . $state;
    }

    private function oauthKey(string $state): string
    {
        return 'social-auth:oauth1:' . $state;
    }
}
