<?php

namespace App\Http\Resources;

use App\Enums\SocialProvider;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\UserSocial
 */
class LinkedSocialAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        $ageInDays = $this->account_created_at?->diffInDays(now());

        $provider = $this->provider instanceof SocialProvider ? $this->provider->value : $this->provider;

        return [
            'id' => $this->id,
            'provider' => $provider,
            'username' => $this->username,
            'profile_url' => $this->profile_url,
            'avatar_url' => $this->avatar_url,
            'account_created_at' => optional($this->account_created_at)->toIsoString(),
            'age_in_days' => $ageInDays,
            'followers_count' => $this->followers_count,
            'last_synced_at' => optional($this->last_synced_at)->toIsoString(),
            'is_primary' => $this->is_primary,
        ];
    }
}
