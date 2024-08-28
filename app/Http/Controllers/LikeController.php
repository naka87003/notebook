<?php

namespace App\Http\Controllers;

use App\Jobs\SendLikeNotification;
use App\Models\Like;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function like($noteId)
    {
        $exists = Like::where('note_id', $noteId)->where('user_id', Auth::id())->exists();
        if ($exists === false && Note::find($noteId)) {
            Like::create([
                'user_id' => Auth::id(),
                'note_id' => $noteId
            ]);
        }

        $note = Note::find($noteId);

        if (Auth::id() !== $note->user_id) {
            $user = User::find($note->user_id);
            dispatch(new SendLikeNotification($user, Auth::user(), $noteId));
        }
        
        return response()->json();
    }

    public function unlike($note_id)
    {
        Like::where('note_id', $note_id)->where('user_id', Auth::id())->delete();
        return response()->json();
    }
}
