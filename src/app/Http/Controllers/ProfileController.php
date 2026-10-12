<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $props = [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'unreadNotificationCount' => Auth::user()->unreadNotifications->count()
        ];
        return Inertia::render('Profile/Edit', $props);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Upload the user's profile image.
     */
    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'image' => 'image|max:10240'
        ]);

        // 画像ファイルインスタンス取得
        $image = $request->file('image');


        // 画像ファイルの保存場所指定
        if (isset($image)) {
            if ($user->image_path) {
                Storage::disk('public')->delete($user->image_path);
            }
            $user->image_path = $image->store('images/user', 'public');
            // 画像のサイズを調整
            $manager = new ImageManager(Driver::class);
            $imgPath = storage_path('app/public/' . $user->image_path);
            $image = $manager->read($imgPath);
            $image->scaleDown(width: 100);
            $image->save(storage_path('app/public/' . $user->image_path));
        }
        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's profile image.
     */
    public function deleteImage(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->image_path) {
            Storage::disk('public')->delete($user->image_path);
        }
        $user->image_path = null;

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('login')->with('status', 'Account Deleted.');
    }
}
