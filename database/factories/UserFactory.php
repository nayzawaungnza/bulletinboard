<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'profile_path' => 'profiles/default.png',
            'role' => $this->faker->randomElement([0, 1,1,1]),
            'dob' => $this->faker->date(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'lock_flag' => $this->faker->randomElement([0, 1]),
            'lock_count' => $this->faker->numberBetween(0, 3),
            'last_lock_at' => $this->faker->optional()->dateTime(),
            'last_login_at' => $this->faker->optional()->dateTime(),
            'create_user_id' => null,
            'updated_user_id' => null,
            'deleted_user_id' => null,
            'remember_token' => Str::random(10),
        ];
    }
}