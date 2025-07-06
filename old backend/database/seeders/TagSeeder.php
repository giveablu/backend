<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tags')->insert([
            [
                'name' => 'Shoe Drive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cookie Dough',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tree Planting Day',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Autographed Memorabilia Auction',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Online Shopping',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spelling Bee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dance Marathon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
