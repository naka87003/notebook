<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Like;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TimelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $props = [
            'unreadNotificationCount' => Auth::user()->unreadNotifications->count()
        ];
        if (isset($request->user) && is_numeric($request->user)) {
            $user = User::find((int)$request->user);
            if ($user) {
                $props['user'] = (int)$request->user;
                $props['userItem'] = $user;
            }
        }
        if (isset($request->note) && is_numeric($request->note)) {
            $note = Note::with(['user', 'category', 'status', 'tag', 'likes'])->withCount(['likes', 'comments'])->find((int)$request->note);
            if ($note) {
                $props['note'] = $note;
            }
        }
        return Inertia::render('Timeline', $props);
    }
}
