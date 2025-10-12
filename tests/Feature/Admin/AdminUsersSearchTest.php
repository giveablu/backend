<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AdminUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_search_filters_results(): void
    {
        $admin = User::factory()->admin()->create();
        $alice = User::factory()->create(['name' => 'Alice Example']);
        $bob = User::factory()->create(['name' => 'Bob Example']);

        $this->actingAs($admin);

        Livewire::test(AdminUser::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Example')
            ->assertDontSee('Bob Example');

        $this->assertNotNull($alice->fresh()->search_id);

        Livewire::test(AdminUser::class)
            ->set('search', (string) $bob->id)
            ->assertSee('Bob Example')
            ->assertDontSee('Alice Example');
    }
}
