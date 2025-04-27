<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'role' => $faker->randomElement(['donor', 'receiver', 'admin']),
                'phone_verified_at' => $faker->optional()->dateTime,
                'email_verified_at' => $faker->optional()->dateTime,
                'device_token' => $faker->optional()->text,
                'password' => bcrypt('password'),
                'photo' => $faker->optional()->imageUrl,
                'gender' => $faker->randomElement(['male', 'female']),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo "Inserted user $index\n";
        }
    }
}
