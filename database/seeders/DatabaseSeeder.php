<?php

namespace Database\Seeders;

use App\Models\Post;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // Core system setup first
            AdminUserSeeder::class,
            
        ]);

        User::factory(32)->create()->each(function ($user) {
        Post::factory(5)->create([
            'create_user_id' => $user->id,
        ]);
    });
    }
}