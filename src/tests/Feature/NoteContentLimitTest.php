<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NoteContentLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_limit_is_shared_with_inertia(): void
    {
        config(['notes.content_max_length' => 8]);

        $this->get(route('login'))->assertInertia(
            fn (Assert $page) => $page->component('Auth/Login')->where('noteContentMaxLength', 8)
        );
    }

    public function test_configured_limit_is_enforced_when_creating_and_updating_notes(): void
    {
        config(['notes.content_max_length' => 8]);
        $this->seed([CategorySeeder::class, StatusSeeder::class]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $attributes = ['content' => str_repeat('あ', 8), 'category' => 1, 'public' => true];

        $this->post(route('notes.store'), $attributes)->assertSessionHasNoErrors();
        $note = Note::firstOrFail();
        $this->assertSame($attributes['content'], $note->content);

        $tooLong = [...$attributes, 'content' => str_repeat('あ', 9)];
        $this->post(route('notes.store'), $tooLong)->assertSessionHasErrors('content');
        $this->assertSame(1, Note::count());

        $updated = [...$attributes, 'content' => str_repeat('い', 8)];
        $this->put(route('notes.update', $note), $updated)->assertSessionHasNoErrors();
        $this->assertSame($updated['content'], $note->fresh()->content);

        $this->put(route('notes.update', $note), $tooLong)->assertSessionHasErrors('content');
        $this->assertSame($updated['content'], $note->fresh()->content);
    }
}
