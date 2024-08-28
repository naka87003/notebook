<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function users(Request $request)
    {
        $query =  User::whereLike('name', "%{$request->search}%");
        $users = $query->orderBy('updated_at', 'DESC')->limit(5)->get();
        return response()->json($users);
    }

    public function user(string $id)
    {
        $user = User::withCount(['followees', 'followers'])->find((int)$id);
        $user->followedByLoginUser = Follow::where('followee_id', (int)$id)->where('follower_id', Auth::id())->exists();
        return response()->json($user);
    }

    public function followers(User $user, Request $request)
    {
        $query = $user->followers();

        if (is_numeric($request->offset)) {
            $query->offset($request->offset);
        }

        $followers = $query->orderByPivot('created_at', 'desc')->get();

        return response()->json($followers);
    }

    public function followees(User $user, Request $request)
    {
        $query = $user->followees();

        if (is_numeric($request->offset)) {
            $query->offset($request->offset);
        }

        $followees = $query->orderByPivot('created_at', 'desc')->get();

        return response()->json($followees);
    }
}
