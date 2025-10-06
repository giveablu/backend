<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorHomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_donor_home_endpoint_returns_paginated_recipients(): void
    {
        $donor = User::query()->where('email', 'donor@blu.test')->firstOrFail();

        Sanctum::actingAs($donor, ['*']);

        $response = $this->getJson('/api/donor-account/home');

        $response
            ->assertOk()
            ->assertJson(
                [
                    'response' => true,
                    'message' => ['Donor Home'],
                ]
            )
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'activity',
                        'receiver_name',
                        'id',
                        'amount',
                        'biography',
                        'date_time',
                        'image',
                        'tags',
                    ],
                ],
            ]);
    }
}
