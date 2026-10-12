<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->paragraphs(rand(1, rand(1, 10)), true),
            'user_id' => User::all()->random()->id,
            'note_id' => Note::where('category_id', 1)->get()->random()->id,
            'created_at' => fake()->dateTimeBetween('-3 year'),
            'updated_at' => fake()->dateTimeBetween('-3 year')
        ];
    }
}
