<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
//use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(5),
            'status' => $this->faker->randomElement([0, 1]), // 0=Inactive, 1=Active
            'create_user_id' => null, // will be set when attached to a user
            'updated_user_id' => null,
            'deleted_user_id' => null,
        ];
    }
}