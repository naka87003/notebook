<?php

namespace App\Http\Controllers;

use App\Models\EmailPreference;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PreferenceController extends Controller
{
    /**
     * Display the user's preference form.
     */
    public function edit(): Response
    {
        $props = [
            'status' => session('status'),
            'unreadNotificationCount' => Auth::user()->unreadNotifications->count(),
            'emailPreferences' => EmailPreference::where('user_id', Auth::id())->get()
        ];
        return Inertia::render('Preferences', $props);
    }

    /**
     * Update the user's preference information.
     */
    public function update(Request $request): RedirectResponse
    {
        foreach ($request->emailPreferences as $key => $value) {
            $request->validate([
                $key => 'boolean'
            ]);
            EmailPreference::where('user_id', Auth::id())->where('type', $key)->update(['value' =>  $value]);
        }

        return Redirect::route('preferences.edit');
    }
}
