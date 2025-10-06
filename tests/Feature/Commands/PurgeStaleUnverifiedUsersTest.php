<?php

namespace Tests\Feature\Commands;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PurgeStaleUnverifiedUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_unverified_users_are_removed(): void
    {
        Config::set('cleanup.unverified_user_retention_days', 7);
        Config::set('cleanup.unverified_user_chunk_size', 50);

        $staleUser = User::factory()->unverified()->create([
            'role' => 'donor',
            'phone' => '1000000000',
            'phone_verified_at' => null,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ]);

        Otp::create([
            'user_id' => $staleUser->id,
            'otp' => '123456',
            'phone' => $staleUser->phone,
            'email' => $staleUser->email,
            'expire' => Carbon::now()->subDays(9),
        ]);

        $verifiedUser = User::factory()->create([
            'role' => 'donor',
            'phone' => '2000000000',
            'phone_verified_at' => Carbon::now(),
            'created_at' => Carbon::now()->subDays(30),
        ]);

        $recentUser = User::factory()->unverified()->create([
            'role' => 'donor',
            'phone' => '3000000000',
            'phone_verified_at' => null,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        Artisan::call('accounts:purge-unverified');

        $this->assertDatabaseMissing('users', ['id' => $staleUser->id]);
        $this->assertDatabaseMissing('otps', ['user_id' => $staleUser->id]);

        $this->assertDatabaseHas('users', ['id' => $verifiedUser->id]);
        $this->assertDatabaseHas('users', ['id' => $recentUser->id]);
    }

    public function test_dry_run_does_not_delete_any_records(): void
    {
        Config::set('cleanup.unverified_user_retention_days', 7);

        $staleUser = User::factory()->unverified()->create([
            'role' => 'receiver',
            'phone' => '4000000000',
            'phone_verified_at' => null,
            'created_at' => Carbon::now()->subDays(10),
        ]);

        Artisan::call('accounts:purge-unverified', ['--dry-run' => true]);

        $this->assertDatabaseHas('users', ['id' => $staleUser->id]);
    }
}
