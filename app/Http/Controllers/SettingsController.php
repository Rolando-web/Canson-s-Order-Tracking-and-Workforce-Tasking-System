<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        return view('pages.settings', compact('user', 'firstName', 'lastName'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'profile_image'    => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = trim($validated['first_name'] . ' ' . $validated['last_name']);

        // Password change
        if (!empty($validated['new_password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        if ($request->hasFile('profile_image')) {
            Storage::disk('public')->makeDirectory('Profile');

            if (!empty($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->profile_image = $request->file('profile_image')->store('Profile', 'public');
        }

        $user->save();

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
