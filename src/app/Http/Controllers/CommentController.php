<?php

namespace App\Http\Controllers;

use App\Jobs\SendCommentNotificationEmail;
use App\Models\Comment;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $noteId, Request $request)
    {
        $skip = is_numeric($request->offset) ? $request->offset : 0;
        $comments = Comment::where('note_id', $noteId)->orderBy('created_at', 'DESC')->offset($skip)->limit(20)->with(['user'])->withCount('replies')->get();
        return response()->json($comments);
    }

    public function store(string $noteId, Request $request)
    {
        $request->validate([
            'comment' => 'required|max:160',
        ]);
        $comment = Comment::create([
            'content' => $request->comment,
            'user_id' => Auth::id(),
            'note_id' => $noteId,
        ]);

        $note = Note::find($noteId);

        if (Auth::id() !== $note->user_id) {
            $user = User::find($note->user_id);
            dispatch(new SendCommentNotificationEmail($user, Auth::user(), $comment));
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json();
    }

    /**
     * commentを1件取得
     */
    public function comment(string $id)
    {
        $comment = Comment::with(['user'])->withCount('replies')->find($id);
        return response()->json($comment);
    }
}
