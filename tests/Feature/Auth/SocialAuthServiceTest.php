<?php

namespace Tests\Feature\Auth;

use App\Enums\SocialProvider;
use App\Enums\SocialVerificationStatus;
use App\Models\SocialVerificationEvent;
use App\Services\SocialAuth\ArraySocialiteUser;
use App\Services\SocialAuth\SocialAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SocialAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_user_and_social_account_from_payload(): void
    {
        $service = app(SocialAccountService::class);

        $socialUser = new ArraySocialiteUser(
            id: Str::uuid()->toString(),
            email: 'recipient@example.com',
            name: 'Recipient Example',
            nickname: 'recipient',
            avatar: 'https://example.com/avatar.jpg',
            raw: [
                'location' => 'Nairobi, Kenya',
                'description' => 'Helping my community',
            ],
        );

        $result = $service->handleAuthentication(
            SocialProvider::Facebook,
            'receiver',
            $socialUser,
        );

        $this->assertSame('receiver', $result['user']->role);
        $this->assertEquals('Recipient Example', $result['user']->fresh()->name);
        $this->assertNotNull($result['social']->id);
        $this->assertTrue($result['social']->is_primary);
        $this->assertSame('Nairobi, Kenya', $result['user']->fresh()->city);
        $this->assertEquals(SocialVerificationStatus::InsufficientData, $result['status']);
        $this->assertEmpty($result['warnings']);
    }

    public function test_it_marks_account_verified_when_old_enough(): void
    {
        $service = app(SocialAccountService::class);

        $socialUser = new ArraySocialiteUser(
            id: 'social-123',
            email: 'donor@example.com',
            name: 'Donor Example',
            nickname: 'donorexample',
            avatar: null,
            raw: [
                'metadata' => ['creationTime' => now()->subYears(2)->toIso8601String()],
            ],
        );

        $result = $service->handleAuthentication(
            SocialProvider::Google,
            'donor',
            $socialUser,
        );

        $user = $result['user']->fresh();

        $this->assertEquals(SocialVerificationStatus::Verified, $result['status']);
        $this->assertNotNull($user->social_verified_at);
        $this->assertTrue(
            SocialVerificationEvent::where('user_id', $user->id)
                ->where('status', SocialVerificationStatus::Verified)
                ->exists()
        );
    }

    public function test_it_flags_new_accounts_for_review(): void
    {
        $service = app(SocialAccountService::class);

        $socialUser = new ArraySocialiteUser(
            id: 'new-account',
            email: 'user@example.com',
            name: 'New Account',
            nickname: 'newaccount',
            avatar: null,
            raw: [
                'created_at' => now()->subMonths(2)->toIso8601String(),
            ],
        );

        $result = $service->handleAuthentication(
            SocialProvider::X,
            'receiver',
            $socialUser,
        );

        $this->assertEquals(SocialVerificationStatus::NeedsReview, $result['status']);
        $this->assertNotEmpty($result['warnings']);
    }
}
