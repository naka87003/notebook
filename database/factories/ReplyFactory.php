<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reply>
 */
class ReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $comment = Comment::all()->random();
        return [
            'content' => fake()->paragraphs(rand(1, rand(1, 10)), true),
            'user_id' => User::all()->random()->id,
            'comment_id' => $comment->id,
            'created_at' => fake()->dateTimeBetween($comment->created_at, 'now'),
            'updated_at' => fake()->dateTimeBetween($comment->created_at, 'now')
        ];
    }

    public function replyTo(): static
    {
        return $this->state(function (array $attributes) {
            $reply = Reply::all()->random();
            $comment = Comment::find($reply->comment_id);
            return [
                'content' => fake()->paragraphs(rand(1, rand(1, 10)), true),
                'user_id' => User::all()->random()->id,
                'comment_id' => $comment->id,
                'reply_to' => $reply->user_id,
                'created_at' => fake()->dateTimeBetween($reply->created_at, 'now'),
                'updated_at' => fake()->dateTimeBetween($reply->created_at, 'now')
            ];
        });
    }
}
