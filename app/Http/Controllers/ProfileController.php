<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('content.pages.pages-profile-user', [
            'profileUser' => $request->user(),
            'activities' => \App\Models\ActivityRecord::with('user')->where('user_id', $request->user()->id)->latest()->paginate(15)
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        // Only admins can submit role and department
        if ($user->isAdmin() || $user->isOwner()) {
            $rules['role'] = ['nullable', Rule::in(['owner', 'sales_engineer', 'customer'])];
            $rules['department'] = ['nullable', 'string', 'max:120'];
            $rules['status'] = ['nullable', Rule::in(['active', 'inactive'])];
        }

        $data = $request->validate($rules);

        // Self-Service Restrictions: Explicitly strip out role, department, and status if user is not Admin/Owner
        if (!$user->isAdmin() && !$user->isOwner()) {
            unset($data['role'], $data['department'], $data['status'], $data['monthly_target']);
        }

        if (empty($data['name']) && (!empty($data['first_name']) || !empty($data['last_name']))) {
            $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // Handle Password update if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $this->audit('updated_profile', get_class($user), $user->id);

        return back()->with('status', 'Profile updated successfully.');
    }
}