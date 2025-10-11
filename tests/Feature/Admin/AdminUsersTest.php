<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AdminUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_route_requires_authentication(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_users_cannot_access_admin_users_route(): void
    {
        $user = User::factory()->create([
            'role' => 'donor',
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_users_component_supports_status_filtering(): void
    {
        $admin = User::factory()->admin()->create();
        $active = User::factory()->create([
            'name' => 'Active Donor',
            'role' => 'donor',
            'status' => 'active',
        ]);
        $suspended = User::factory()->receiver()->suspended()->create([
            'name' => 'Suspended Receiver',
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminUser::class)
            ->set('statusFilter', 'suspended')
            ->assertSee('Suspended Receiver')
            ->assertDontSee('Active Donor');

        $this->assertSame('active', $active->fresh()->status);
        $this->assertSame('suspended', $suspended->fresh()->status);
    }

    public function test_admin_can_toggle_user_status_from_component(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->receiver()->create([
            'status' => 'active',
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminUser::class)
            ->call('selectUser', $user->id)
            ->call('toggleStatus')
            ->assertSet('editForm.status', 'suspended');

        $this->assertSame('suspended', $user->fresh()->status);
    }
}
