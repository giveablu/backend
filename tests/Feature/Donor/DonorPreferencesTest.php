<?php

namespace Tests\Feature\Donor;

use App\Models\DonorPreference;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_donor_home_respects_saved_preferences(): void
    {
        $donor = User::factory()->create([
            'role' => 'donor',
        ]);

        $tagEducation = Tag::create(['name' => 'Education']);
        $tagHealth = Tag::create(['name' => 'Health']);

        $matchingReceiver = User::factory()->create([
            'role' => 'receiver',
            'country' => 'Kenya',
            'region' => 'Nairobi County',
            'city' => 'Nairobi',
        ]);

        $nonMatchingReceiver = User::factory()->create([
            'role' => 'receiver',
            'country' => 'Nigeria',
            'region' => 'Lagos',
            'city' => 'Lagos',
        ]);

        $matchingPost = Post::create([
            'user_id' => $matchingReceiver->id,
            'amount' => '50.00',
            'biography' => 'Support education',
            'image' => 'images/recipient-education.jpg',
        ]);
        $matchingPost->tags()->attach($tagEducation->id);

        $nonMatchingPost = Post::create([
            'user_id' => $nonMatchingReceiver->id,
            'amount' => '60.00',
            'biography' => 'Support health',
            'image' => 'images/recipient-health.jpg',
        ]);
        $nonMatchingPost->tags()->attach($tagHealth->id);

        DonorPreference::create([
            'user_id' => $donor->id,
            'preferred_country' => 'Kenya',
            'preferred_region' => 'Nairobi County',
            'preferred_city' => 'Nairobi',
            'preferred_hardship_ids' => [$tagEducation->id],
        ]);

        Sanctum::actingAs($donor);

        $response = $this->getJson('/api/donor-account/home');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertSame($matchingPost->id, $data[0]['id']);
    }

    public function test_donor_home_falls_back_to_general_feed_when_no_matches(): void
    {
        $donor = User::factory()->create([
            'role' => 'donor',
        ]);

        $tagEducation = Tag::create(['name' => 'Education']);

        $receiver = User::factory()->create([
            'role' => 'receiver',
            'country' => 'Nigeria',
            'region' => 'Lagos',
            'city' => 'Lagos',
        ]);

        $post = Post::create([
            'user_id' => $receiver->id,
            'amount' => '60.00',
            'biography' => 'Support health',
            'image' => 'images/recipient-health.jpg',
        ]);
        $post->tags()->attach($tagEducation->id);

        DonorPreference::create([
            'user_id' => $donor->id,
            'preferred_country' => 'Kenya',
            'preferred_region' => 'Nairobi County',
            'preferred_city' => 'Nairobi',
            'preferred_hardship_ids' => [$tagEducation->id],
        ]);

        Sanctum::actingAs($donor);

        $response = $this->getJson('/api/donor-account/home');

        $response->assertOk();

        $this->assertSame('fallback', $response->json('preference_status'));

        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertSame($post->id, $data[0]['id']);
    }
}
