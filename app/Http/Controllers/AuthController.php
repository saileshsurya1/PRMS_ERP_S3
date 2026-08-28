<?php

namespace App\Http\Controllers;

use App\Models\MenuAccess;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function create()
    {
        return view('content.authentications.auth-login-cover');
    }

    public function register()
    {
        return view('content.authentications.auth-register-cover');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->isActive()) {
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact the administrator.',
            ])->onlyInput('email');
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended($request->user()->isCustomer() ? route('customer.dashboard') : route('dashboard'));
    }

    public function registerStore(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', Rule::in(['owner', 'sales_engineer', 'customer'])],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'email.unique' => 'This email address is already registered. Please sign in or use a different email.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        // Owner Role Restriction: Only one Owner can register in the system
        if ($data['role'] === 'owner') {
            $ownerExists = User::where('role', 'owner')->exists();
            if ($ownerExists) {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors([
                        'role' => 'Owner already exists. Only one Owner account can be registered.',
                    ]);
            }
        }

        // Profile Photo Upload
        $profilePhotoPath = null;
        if ($request->hasFile('photo')) {
            $profilePhotoPath = $request->file('photo')->store('profile-photos', 'public');
        }

        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);

        $user = User::create([
            'name' => $fullName,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role' => $data['role'],
            'status' => 'active',
            'active' => true,
            'profile_photo_path' => $profilePhotoPath,
            'password' => Hash::make($data['password']),
        ]);

        // Default Access: Newly registered user gets access only to Dashboard (and Profile)
        // All other menu access starts blank/off until Owner assigns it.
        $dashboardMenu = MenuItem::where('route', 'dashboard')->orWhere('route', 'dashboard.home')->first();
        if ($dashboardMenu) {
            MenuAccess::create([
                'menu_item_id' => $dashboardMenu->id,
                'subject_type' => 'user',
                'subject_value' => (string) $user->id,
            ]);
        }

        $this->audit('registered_user', User::class, $user->id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome to PRMS Workspace!');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}