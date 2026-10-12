<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Note;
use App\Models\Reply;
use App\Models\Tag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => 'password'
        ]);

        $this->call([
            CategorySeeder::class,
            StatusSeeder::class,
        ]);

        User::factory(10)->create();
        Tag::factory(1000)->create();
        Note::factory(800)->create();
        Note::factory(400)->event()->create();
        Like::factory(2000)->create();
        Comment::factory(2400)->create();
        Reply::factory(1200)->create();
        Reply::factory(200)->replyTo()->create();
        Reply::factory(200)->replyTo()->create();
        Reply::factory(400)->replyTo()->create();
        Reply::factory(400)->replyTo()->create();
        Reply::factory(400)->replyTo()->create();
        Follow::factory(50)->create();
    }
}
