<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@bulletinboard.com',
            'password' => Hash::make('password'),
            'profile_path' => 'profiles/default.png',
            'role' => 0, // Admin role
            'dob' => now()->subYears(30),
            'phone' => '1234567890',
            'address' => 'Admin Address',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@bulletinboard.com',
            'password' => Hash::make('password'),
            'profile_path' => 'profiles/default.png',
            'role' => 1, // User role
            'dob' => now()->subYears(25),
            'phone' => '0987654321',
            'address' => 'User Address',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}