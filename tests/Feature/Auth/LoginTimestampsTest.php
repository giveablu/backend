<?php

namespace Tests\Feature\Auth;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTimestampsTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_login_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create([
            'email' => 'donor@example.com',
            'password' => Hash::make('secret123'),
            'last_login_at' => null,
        ]);

        $response = $this->postJson('/api/auth/sign-in', [
            'authvalue' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk();

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_otp_verification_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create([
            'email' => 'receiver@example.com',
            'email_verified_at' => null,
            'password' => Hash::make(Str::random(12)),
            'last_login_at' => null,
        ]);

        $otp = Otp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'otp' => '123456',
            'expire' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $user->email,
            'otp' => $otp->otp,
        ]);

        $response->assertOk();

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->last_login_at);
        $this->assertNotNull($fresh->email_verified_at);
    }
}
