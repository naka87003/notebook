<?php

namespace App\Http\Controllers;

use App\Jobs\SendReplyNotificationEmail;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $commentId, Request $request)
    {
        $skip = is_numeric($request->offset) ? $request->offset : 0;
        $replies = Reply::where('comment_id', $commentId)->orderBy('created_at', 'DESC')->offset($skip)->limit(20)->with(['user', 'addressee'])->get();
        return response()->json($replies);
    }

    public function store(string $commentId, Request $request)
    {
        $request->validate([
            'reply' => 'required|max:160',
        ]);

        $props = [
            'content' => $request->reply,
            'user_id' => Auth::id(),
            'comment_id' => $commentId,
        ];

        if (isset($request->reply_to) && is_numeric($request->reply_to)) {
            $props['reply_to'] = $request->reply_to;
        }

        $reply = Reply::create($props);

        $comment = Comment::find($reply->comment_id);

        if (isset($reply->reply_to) && is_numeric($reply->reply_to)) {
            if (Auth::id() !== $reply->reply_to) {
                $user = User::find($reply->reply_to);
                dispatch(new SendReplyNotificationEmail($user, Auth::user(), $comment, $reply));
            }
        } else {
            if (Auth::id() !== $comment->user_id) {
                $user = User::find($comment->user_id);
                dispatch(new SendReplyNotificationEmail($user, Auth::user(), $comment, $reply));
            }
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
        $reply->delete();
        return response()->json();
    }
}
