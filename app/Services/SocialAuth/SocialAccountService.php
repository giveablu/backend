<?php

namespace App\Services\SocialAuth;

use App\Enums\SocialProvider;
use App\Enums\SocialVerificationStatus;
use App\Models\SocialVerificationEvent;
use App\Models\User;
use App\Models\UserSocial;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAccountService
{
    /**
     * Link a social account to a user or create a user if needed.
     *
     * @return array{user: User, social: UserSocial, status: SocialVerificationStatus, warnings: array<int, string>}
     */
    public function handleAuthentication(
        SocialProvider $provider,
        string $role,
        SocialiteUser $socialiteUser,
        ?User $linkUser = null,
        ?string $deviceToken = null
    ): array {
        return DB::transaction(function () use ($provider, $role, $socialiteUser, $linkUser, $deviceToken) {
            $normalized = $this->normalizeSocialiteUser($provider, $socialiteUser);

            $user = $this->resolveUser($normalized, $role, $provider, $linkUser);

            $social = $this->upsertSocialAccount($user, $provider, $normalized);

            $warnings = $this->syncUserProfile($user, $normalized, $deviceToken);

            [$status, $statusWarnings] = $this->resolveVerificationStatus($user, $social, $normalized['account_created_at']);

            $warnings = array_merge($warnings, $statusWarnings);

            return compact('user', 'social', 'status', 'warnings');
        });
    }

    /**
     * @return array{provider_user_id: string, email: string, name: string, nickname: ?string, avatar: ?string, raw: array<string, mixed>, username: ?string, profile_url: ?string, followers_count: ?int, account_created_at: ?CarbonImmutable, bio: ?string, location: ?string}
     */
    private function normalizeSocialiteUser(SocialProvider $provider, SocialiteUser $socialiteUser): array
    {
        $raw = $socialiteUser->user ?? [];

        $providerUserId = (string) $socialiteUser->getId();
        $email = $socialiteUser->getEmail() ?? $this->fallbackEmail($provider, $providerUserId);
        $name = $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? Str::headline($provider->value . ' user');
        $nickname = $socialiteUser->getNickname();
        $avatar = $socialiteUser->getAvatar();
        $username = $this->resolveUsername($provider, $socialiteUser, $raw);
        $profileUrl = $this->resolveProfileUrl($provider, $providerUserId, $username, $raw);
        $followers = $this->resolveFollowersCount($provider, $raw);
        $accountCreatedAt = $this->resolveAccountCreatedAt($provider, $raw);
        $bio = $this->resolveBio($provider, $raw);
        $location = $this->resolveLocation($provider, $raw);

        return [
            'provider_user_id' => $providerUserId,
            'email' => $email,
            'name' => $name,
            'nickname' => $nickname,
            'avatar' => $avatar,
            'raw' => $raw,
            'username' => $username,
            'profile_url' => $profileUrl,
            'followers_count' => $followers,
            'account_created_at' => $accountCreatedAt,
            'bio' => $bio,
            'location' => $location,
        ];
    }

    private function resolveUser(array $normalized, string $role, SocialProvider $provider, ?User $linkUser = null): User
    {
        if ($linkUser) {
            if ($linkUser->role !== $role && $linkUser->role !== 'admin') {
                throw new \RuntimeException('Authenticated user role mismatch.');
            }

            return $linkUser;
        }

        $user = User::whereHas('socialAccounts', function ($query) use ($normalized, $provider) {
            $query->where('provider_user_id', $normalized['provider_user_id'])
                ->where('provider', $provider->value);
        })->first();

        if (! $user) {
            $user = User::where('email', $normalized['email'])->first();
        }

        if ($user) {
            if ($user->role !== $role && $user->role !== 'admin') {
                throw new \RuntimeException('This email is already registered with a different role.');
            }

            return $user;
        }

        return User::create([
            'name' => $normalized['name'],
            'email' => $normalized['email'],
            'role' => $role,
            'photo' => $normalized['avatar'],
        ]);
    }

    private function upsertSocialAccount(User $user, SocialProvider $provider, array $normalized): UserSocial
    {
        $user->socialAccounts()->update(['is_primary' => false]);

        return $user->socialAccounts()->updateOrCreate(
            [
                'provider' => $provider->value,
                'provider_user_id' => $normalized['provider_user_id'],
            ],
            [
                'username' => $normalized['username'],
                'profile_url' => $normalized['profile_url'],
                'avatar_url' => $normalized['avatar'],
                'account_created_at' => $normalized['account_created_at'],
                'followers_count' => $normalized['followers_count'],
                'raw_payload' => $normalized['raw'],
                'last_synced_at' => now(),
                'is_primary' => true,
            ]
        );
    }

    private function syncUserProfile(User $user, array $normalized, ?string $deviceToken = null): array
    {
        $updates = [];
        $warnings = [];

        if (! $user->photo && $normalized['avatar']) {
            $updates['photo'] = $normalized['avatar'];
        }

        if ($normalized['name'] && $user->name !== $normalized['name']) {
            $updates['name'] = $normalized['name'];
        }

        if ($normalized['location'] && ! $user->city) {
            $updates['city'] = $normalized['location'];
        }

        if ($normalized['bio'] && ! $user->profile_description) {
            $updates['profile_description'] = $normalized['bio'];
        }

        if ($deviceToken) {
            $updates['device_token'] = $deviceToken;
        }

        $updates['last_login_at'] = now();

        if ($updates) {
            $user->fill($updates);
            $user->save();
        }

        return $warnings;
    }

    /**
     * @return array{0: SocialVerificationStatus, 1: array<int, string>}
     */
    private function resolveVerificationStatus(User $user, UserSocial $social, ?CarbonImmutable $accountCreatedAt): array
    {
        $previousStatus = $user->social_verification_status ?? SocialVerificationStatus::Pending;
        $status = SocialVerificationStatus::InsufficientData;
        $reason = null;
        $metadata = [];
        $warnings = [];

        if ($accountCreatedAt) {
            $ageDays = $accountCreatedAt->diffInDays(now());
            $metadata['account_age_days'] = $ageDays;

            if ($ageDays >= 365) {
                $status = SocialVerificationStatus::Verified;
            } else {
                $status = SocialVerificationStatus::NeedsReview;
                $reason = 'Connected account is younger than one year.';
            }
        }

        if ($status !== $previousStatus) {
            $user->forceFill([
                'social_verification_status' => $status,
                'social_verified_at' => $status === SocialVerificationStatus::Verified ? now() : null,
            ])->save();

            SocialVerificationEvent::create([
                'user_id' => $user->id,
                'user_social_id' => $social->id,
                'status' => $status,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);
        }

        if ($reason) {
            $warnings[] = $reason;
        }

        return [$status, $warnings];
    }

    private function resolveUsername(SocialProvider $provider, SocialiteUser $user, array $raw): ?string
    {
        return $user->getNickname()
            ?? Arr::get($raw, 'username')
            ?? Arr::get($raw, 'handle')
            ?? ($provider === SocialProvider::Facebook ? Arr::get($raw, 'name') : null);
    }

    private function resolveProfileUrl(SocialProvider $provider, string $id, ?string $username, array $raw): ?string
    {
        return match ($provider) {
            SocialProvider::Facebook => $username ? 'https://www.facebook.com/' . $username : 'https://www.facebook.com/app_scoped_user_id/' . $id,
            SocialProvider::Instagram => $username ? 'https://www.instagram.com/' . $username : null,
            SocialProvider::X => $username ? 'https://x.com/' . $username : Arr::get($raw, 'url'),
            SocialProvider::Google => Arr::get($raw, 'profile') ?? null,
        };
    }

    private function resolveFollowersCount(SocialProvider $provider, array $raw): ?int
    {
        $keys = match ($provider) {
            SocialProvider::Facebook => ['followers_count', 'friends'],
            SocialProvider::Instagram => ['followers_count', 'edge_followed_by.count'],
            SocialProvider::X => ['followers_count', 'public_metrics.followers_count'],
            SocialProvider::Google => [],
        };

        foreach ($keys as $key) {
            $value = Arr::get($raw, $key);
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function resolveAccountCreatedAt(SocialProvider $provider, array $raw): ?CarbonImmutable
    {
        $key = match ($provider) {
            SocialProvider::Facebook => 'created_time',
            SocialProvider::Instagram => 'created_time',
            SocialProvider::X => 'created_at',
            SocialProvider::Google => 'metadata.creationTime',
        };

        $value = Arr::get($raw, $key);

        if (! $value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveBio(SocialProvider $provider, array $raw): ?string
    {
        return match ($provider) {
            SocialProvider::Facebook => Arr::get($raw, 'bio'),
            SocialProvider::Instagram => Arr::get($raw, 'biography'),
            SocialProvider::X => Arr::get($raw, 'description'),
            SocialProvider::Google => Arr::get($raw, 'tagline'),
        };
    }

    private function resolveLocation(SocialProvider $provider, array $raw): ?string
    {
        return match ($provider) {
            SocialProvider::Facebook => Arr::get($raw, 'location.name') ?? Arr::get($raw, 'location'),
            SocialProvider::Instagram => Arr::get($raw, 'location') ?? Arr::get($raw, 'city_name'),
            SocialProvider::X => Arr::get($raw, 'location'),
            SocialProvider::Google => Arr::get($raw, 'placesLived.0.value'),
        };
    }

    private function fallbackEmail(SocialProvider $provider, string $providerUserId): string
    {
        return sprintf('%s_%s@oauth.giveablu.test', $provider->value, $providerUserId);
    }
}
