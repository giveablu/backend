<?php

namespace Tests\Feature;

use App\Models\ImpactSnapshot;
use Database\Seeders\ImpactExampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpactEstimateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ImpactExampleSeeder::class);
    }

    public function test_it_returns_country_specific_example(): void
    {
        $response = $this->getJson('/api/impact/estimate?country=AM&amount=12');

        $response
            ->assertStatus(200)
            ->assertJsonPath('response', true)
            ->assertJsonPath('data.example.country', 'AM')
            ->assertJsonPath('data.example.headline', 'Groceries for a small family');
    }

    public function test_it_falls_back_to_global_when_country_missing(): void
    {
        $response = $this->getJson('/api/impact/estimate?country=ZZ&amount=18');

        $response
            ->assertStatus(200)
            ->assertJsonPath('response', true)
            ->assertJsonPath('data.example.country', 'GL');
    }

    public function test_it_rejects_amount_below_minimum(): void
    {
        $response = $this->getJson('/api/impact/estimate?country=AM&amount=2');

        $response->assertStatus(422);
    }

    public function test_it_rejects_amount_over_maximum(): void
    {
        $response = $this->getJson('/api/impact/estimate?country=AM&amount=60');

        $response->assertStatus(422);
    }

    public function test_it_prefers_snapshot_data_when_available(): void
    {
        ImpactSnapshot::create([
            'country_iso' => 'AM',
            'category' => 'general',
            'min_usd' => 5,
            'max_usd' => 15,
            'headline' => 'Fresh produce basket',
            'description' => 'Based on weekly market survey.',
            'icon' => 'groceries',
            'local_currency' => 'AMD',
            'local_amount' => 4200,
            'source' => 'OPS weekly feed',
            'metadata' => [
                'local_note' => 'Collected from Yerevan vendors',
            ],
            'observed_at' => now(),
        ]);

        $response = $this->getJson('/api/impact/estimate?country=AM&amount=10');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.example.headline', 'Fresh produce basket')
            ->assertJsonPath('data.example.source', 'OPS weekly feed')
            ->assertJsonPath('data.amount.local_currency', 'AMD');
    }
}
