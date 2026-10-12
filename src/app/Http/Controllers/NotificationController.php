<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = User::find(Auth::id());
        $skip = is_numeric($request->offset) ? $request->offset : 0;
        $notifications = $user->notifications()->offset($skip)->limit(20)->get();

        $notifications = $notifications->map(function ($item) {
            $user = User::find($item->data['user']['id']);
            if ($user) {
                $item->user = $user;
                return $item;
            }
        })->filter(function ($item) {
            return $item !== null;
        })->values();

        return response()->json($notifications);
    }

    public function markAsRead($notificationId)
    {
        $userUnreadNotification = Auth::user()
            ->unreadNotifications
            ->where('id', $notificationId)
            ->first();

        if ($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
        }

        return response()->json();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json();
    }
}
