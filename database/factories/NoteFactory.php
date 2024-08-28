<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::all()->random()->id;
        return [
            'title' => fake()->randomElement([fake()->sentence(rand(1, 10)), null]),
            'content' => fake()->paragraphs(rand(1, rand(1, 10)), true),
            'public' => true,
            'user_id' => $userId,
            'category_id' => 1,
            'status_id' => 1,
            'tag_id' => fake()->randomElement([Tag::where('user_id', $userId)->inRandomOrder()->first()->id, null, null]),
            'created_at' => fake()->dateTimeBetween('-3 year'),
            'updated_at' => fake()->dateTimeBetween('-3 year')
        ];
    }


    public function event(): static
    {
        return $this->state(function (array $attributes) {
            $userId = User::all()->random()->id;
            $dt = Carbon::parse(fake()->dateTimeBetween('now', '+3 month'));
            $start = $dt->toDateTimeString();
            $end = $dt->addHours(rand(1, 10))->toDateTimeString();

            return [
                'title' => fake()->sentence(rand(1, 10)),
                'content' => fake()->randomElement([fake()->paragraphs(rand(1, rand(1, 10)), true), null, null]),
                'user_id' => $userId,
                'category_id' => 3,
                'status_id' => 1,
                'starts_at' => $start,
                'ends_at' => $end,
                'tag_id' => fake()->randomElement([Tag::where('user_id', $userId)->inRandomOrder()->first()->id, null]),
            ];
        });
    }
}
