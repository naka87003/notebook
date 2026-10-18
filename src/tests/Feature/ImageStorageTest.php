<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_images_are_stored_and_private_images_are_only_accessible_to_the_owner(): void
    {
        config(['filesystems.images' => 'images']);
        $disk = Storage::fake('images');
        $this->seed([CategorySeeder::class, StatusSeeder::class]);
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $other */
        $other = User::factory()->create();

        $this->actingAs($owner)->post(route('notes.store'), [
            'content' => 'Private note', 'category' => 1, 'public' => false,
            'image' => UploadedFile::fake()->image('photo.jpg', 900, 500),
        ])->assertSessionHasNoErrors();

        $note = Note::firstOrFail();
        $disk->assertExists($note->image_path);
        $this->get('/media/'.$note->image_path)
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, private');
        $this->actingAs($other)->get('/media/'.$note->image_path)->assertNotFound();

        $note->update(['public' => true]);
        $this->get('/media/'.$note->image_path)->assertOk();
        $this->get('/media/images/notes/missing.jpg')->assertNotFound();
    }

    public function test_profile_images_can_be_uploaded_viewed_and_deleted(): void
    {
        config(['filesystems.images' => 'images']);
        $disk = Storage::fake('images');
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('profile.upload'), [
            'image' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ])->assertSessionHasNoErrors();

        $path = $user->fresh()->image_path;
        $disk->assertExists($path);
        $this->get('/media/'.$path)
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, private');
        $this->delete(route('profile.delete.image'))->assertRedirect();
        $disk->assertMissing($path);
    }
}
