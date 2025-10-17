<?php

namespace App\Services\SocialAuth;

use App\Enums\SocialProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Str;

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

    private function key(string $state): string
    {
        return 'social-auth:state:' . $state;
    }
}
