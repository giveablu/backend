<?php

namespace App\Services\SocialAuth;

use App\Enums\SocialProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;
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

        Log::debug('Social state generated', [
            'provider' => $provider->value,
            'intent' => $intent,
            'state_hash' => $this->hash($state),
            'payload_keys' => array_keys($payload),
        ]);

        return $state;
    }

    public function resolve(string $state): ?array
    {
        $payload = $this->cache->get($this->key($state));

        Log::debug('Social state resolved', [
            'state_hash' => $this->hash($state),
            'payload_present' => (bool) $payload,
            'payload_provider' => $payload['provider'] ?? null,
            'payload_role' => $payload['role'] ?? null,
        ]);

        return $payload;
    }

    public function forget(string $state): void
    {
        Log::debug('Social state forgotten', [
            'state_hash' => $this->hash($state),
        ]);
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

        Log::debug('OAuth1 temp credentials stored', [
            'state_hash' => $this->hash($state),
            'identifier_tail' => substr($credentials->getIdentifier(), -6),
        ]);
    }

    public function pullOauth1Credentials(string $state): ?TemporaryCredentials
    {
        $payload = $this->cache->pull($this->oauthKey($state));

        Log::debug('OAuth1 temp credentials pulled', [
            'state_hash' => $this->hash($state),
            'payload_present' => (bool) $payload,
        ]);

        if (! $payload || empty($payload['identifier']) || empty($payload['secret'])) {
            Log::warning('OAuth1 temp credentials missing or incomplete', [
                'state_hash' => $this->hash($state),
            ]);
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

    private function hash(string $state): string
    {
        return substr(hash('sha256', $state), 0, 12);
    }
}
