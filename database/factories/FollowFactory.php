<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'follower_id' => User::all()->random()->id,
            'followee_id' => User::all()->random()->id,
            'created_at' => fake()->dateTimeBetween('-3 year'),
            'updated_at' => fake()->dateTimeBetween('-3 year')
        ];
    }
}
