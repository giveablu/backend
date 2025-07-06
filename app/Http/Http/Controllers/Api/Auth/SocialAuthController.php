<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        $validProviders = ['facebook', 'google', 'twitter', 'instagram', 'linkedin'];
        
        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider, Request $request)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Find or create user
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'photo' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }

            // Create or update social account
            $socialAccount = SocialAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'provider' => $provider,
                ],
                [
                    'provider_id' => $socialUser->getId(),
                    'username' => $socialUser->getNickname() ?? $socialUser->getName(),
                    'profile_url' => $this->getProfileUrl($provider, $socialUser),
                    'profile_photo' => $socialUser->getAvatar(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'follower_count' => $this->getFollowerCount($provider, $socialUser),
                    'is_verified' => $this->checkVerificationStatus($provider, $socialUser),
                ]
            );

            // Update user's main profile photo if not set
            if (!$user->photo) {
                $user->update(['photo' => $socialUser->getAvatar()]);
            }

            // Calculate trust score
            $trustScore = $this->calculateTrustScore($user);
            $user->update(['trust_score' => $trustScore]);

            Auth::login($user);

            return response()->json([
                'response' => true,
                'message' => ['Social login successful'],
                'data' => $user,
                'meta' => [
                    'access_token' => $user->createToken('auth_token')->plainTextToken,
                    'trust_score' => $trustScore,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'response' => false,
                'message' => ['Social login failed: ' . $e->getMessage()],
            ], 500);
        }
    }

    private function getProfileUrl($provider, $socialUser)
    {
        switch ($provider) {
            case 'facebook':
                return "https://facebook.com/{$socialUser->getId()}";
            case 'twitter':
                return "https://twitter.com/{$socialUser->getNickname()}";
            case 'instagram':
                return "https://instagram.com/{$socialUser->getNickname()}";
            case 'linkedin':
                return "https://linkedin.com/in/{$socialUser->getNickname()}";
            default:
                return null;
        }
    }

    private function getFollowerCount($provider, $socialUser)
    {
        // This would require additional API calls to each platform
        // For now, return null - implement later with proper API calls
        return null;
    }

    private function checkVerificationStatus($provider, $socialUser)
    {
        // Check if account is verified on the platform
        // Implementation depends on each platform's API
        return false; // Default to false for now
    }

    private function calculateTrustScore($user)
    {
        $score = 0;
        $socialAccounts = $user->socialAccounts;

        // Base score for having accounts
        $score += $socialAccounts->count() * 15; // 15 points per account

        foreach ($socialAccounts as $account) {
            // Verified accounts get bonus points
            if ($account->is_verified) {
                $score += 20;
            }

            // Follower count bonus
            if ($account->follower_count) {
                if ($account->follower_count > 1000) $score += 10;
                if ($account->follower_count > 10000) $score += 10;
            }

            // Account age bonus (if we can determine it)
            if ($account->created_at && $account->created_at->diffInMonths(now()) > 12) {
                $score += 10;
            }
        }

        return min($score, 100); // Cap at 100
    }

    public function getPublicVerification($userId)
    {
        $user = User::with('socialAccounts')->findOrFail($userId);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_photo' => $user->photo,
                'join_date' => $user->created_at->format('M Y'),
                'story' => $user->biography ?? 'No story provided',
            ],
            'social_accounts' => $user->socialAccounts->map(function ($account) {
                return [
                    'platform' => $account->provider,
                    'username' => $account->username,
                    'profile_url' => $account->profile_url,
                    'profile_photo' => $account->profile_photo,
                    'is_verified' => $account->is_verified,
                    'follower_count' => $account->follower_count,
                ];
            }),
            'trust_score' => $user->trust_score ?? 0,
        ]);
    }
}
