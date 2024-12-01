<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        \App\Models\Country::create([
            'id' => 1,
            'name' => 'Slovenija',
        ]);

        \App\Models\Person::create([
            'emso' => '1092145690123',
            'name' => 'Žan Zakrajšek',
            'country_id' => 1,
            'age' => 25,
            'description' => 'I love Slovenia',
        ]);
    }
}
