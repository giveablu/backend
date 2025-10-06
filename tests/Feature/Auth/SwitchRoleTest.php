<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SwitchRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_switch_role(): void
    {
        $user = User::factory()->create([
            'role' => 'donor',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/switch-role', ['role' => 'receiver']);

        $response->assertOk();
        $response->assertJsonPath('response', true);
        $response->assertJsonPath('message.0', "Role updated successfully. You're now in receiver mode.");
        $this->assertSame('receiver', $user->fresh()->role);
    }

    public function test_switch_role_returns_message_when_role_is_unchanged(): void
    {
        $user = User::factory()->create([
            'role' => 'receiver',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/switch-role', ['role' => 'receiver']);

        $response->assertOk();
        $response->assertJsonPath('response', true);
        $response->assertJsonPath('message.0', "You're already using the receiver experience.");
        $this->assertSame('receiver', $user->fresh()->role);
    }

    public function test_switch_role_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/switch-role', ['role' => 'donor']);

        $response->assertStatus(401);
        $this->assertSame('Unauthenticated.', $response->json('message'));
    }
}
