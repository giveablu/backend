<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReceiverProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_receiver_can_update_profile_details_with_description_and_location(): void
    {
        $user = User::factory()->create([
            'role' => 'receiver',
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Updated Receiver',
            'gender' => 'female',
            'profile_description' => 'A short introduction for donors.',
            'city' => 'Accra',
            'region' => 'Greater Accra',
            'country' => 'Ghana',
        ];

        $response = $this->postJson('/api/receiver-account/profile/update/detail', $payload);

        $response->assertOk();
        $response->assertJsonPath('response', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Receiver',
            'gender' => 'female',
            'profile_description' => 'A short introduction for donors.',
            'city' => 'Accra',
            'region' => 'Greater Accra',
            'country' => 'Ghana',
        ]);
    }

    public function test_receiver_can_update_story_and_sync_custom_hardships(): void
    {
        $user = User::factory()->create([
            'role' => 'receiver',
        ]);

        Sanctum::actingAs($user);
        Notification::fake();

        $payload = [
            'biography' => 'This is my updated story.',
            'hardships' => [
                'Medical Bills',
                ['name' => 'Job Loss'],
            ],
        ];

        $response = $this->postJson('/api/receiver-account/profile/update/post', $payload);

        $response->assertOk();
        $response->assertJsonPath('response', true);

        $user->refresh();
        $post = $user->post;

        $this->assertNotNull($post, 'Receiver post should exist after update.');
        $this->assertSame('This is my updated story.', $post->biography);
        $this->assertCount(2, $post->tags);

        $this->assertDatabaseHas('tags', ['name' => 'Medical Bills']);
        $this->assertDatabaseHas('tags', ['name' => 'Job Loss']);

        $this->assertEqualsCanonicalizing(
            ['Medical Bills', 'Job Loss'],
            $post->tags->pluck('name')->all()
        );
    }
}
