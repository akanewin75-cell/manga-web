<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // 1. Fill basic info manually (don't use $request->all() or $request->validated())
        $user->name = $request->input('name');
        $user->email = $request->input('email');

        // 2. Handle profile photo
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            
            if ($file->isValid()) {
                // Generate safe filename
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
                $directory = public_path('profiles');
                
                if (!file_exists($directory)) {
                    @mkdir($directory, 0777, true);
                }

                // Delete old photo
                if ($user->profile_photo) {
                    $oldPath = public_path($user->profile_photo);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                // Use copy() instead of move_uploaded_file() so the temp file stays 
                // physically present until the request ends. This is a fix for 
                // "File does not exist" errors in some Windows/Laravel environments.
                if (@copy($file->getRealPath(), $directory . '/' . $filename)) {
                    $user->profile_photo = 'profiles/' . $filename;
                }

                // CRITICAL: Unset the file from the request
                $request->files->remove('profile_photo');
                $request->offsetUnset('profile_photo');
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Redirect WITHOUT flashing all input (don't use back() with input)
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
