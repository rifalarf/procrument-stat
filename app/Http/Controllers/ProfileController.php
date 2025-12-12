<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => 'nullable|min:6|confirmed',
        ]);

        // If user wants to set a password (e.g. they only had Google login before)
        // We allow them to set it without current_password IF they don't have a password yet.
        if (empty($user->password) && $request->filled('password')) {
             $user->update([
                 'password' => bcrypt($request->password)
             ]);
             return back()->with('success', 'Password set successfully.');
        }

        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password)
            ]);
            return back()->with('success', 'Password updated successfully.');
        }

        return back()->with('info', 'No changes made.');
    }
}
