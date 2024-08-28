<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendFollowNotificationEmail;

class FollowController extends Controller
{
    public function follow($user_id)
    {
        $exists = Follow::where('followee_id', $user_id)->where('follower_id', Auth::id())->exists();
        if ($exists === false && User::find($user_id)) {
            Follow::create([
                'follower_id' => Auth::id(),
                'followee_id' => $user_id

            ]);
        }

        $user = User::find($user_id);

        dispatch(new SendFollowNotificationEmail($user, Auth::user()));
        return response()->json();
    }

    public function unfollow($user_id)
    {
        Follow::where('followee_id', $user_id)->where('follower_id', Auth::id())->delete();
        return response()->json();
    }
}
