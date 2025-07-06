<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'default_amount' => 5,
            'app_version' => 1.0,
            'app_feature' => 'this is feature 1|this is feature 2|this is feature 3|this is feature 4|this is feature 5'
        ]);
    }
}
