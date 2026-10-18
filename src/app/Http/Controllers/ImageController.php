<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Services\ImageStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        if (str_starts_with($path, 'images/notes/')) {
            $allowed = Note::where('image_path', $path)
                ->where(fn ($query) => $query->where('public', true)->orWhere('user_id', $request->user()->id))
                ->exists();
        } elseif (str_starts_with($path, 'images/user/')) {
            $allowed = User::where('image_path', $path)->exists();
        } else {
            $allowed = false;
        }

        abort_unless($allowed && ImageStorage::disk()->exists($path), 404);

        return ImageStorage::disk()->response($path, null, ['Cache-Control' => 'private, max-age=300']);
    }
}
