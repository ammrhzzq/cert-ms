<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.auth', [
            'activeTab' => 'login'
        ]);
    }

    public function showRegister()
    {
        return view('auth.auth', [
            'activeTab' => 'register'
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Hash the password
        $validated['password'] = bcrypt($validated['password']);
        
        // Check if the email is the designated HOD email
        $hodEmail = Config::get('app.hod_email', 'hod@example.com'); // Default value as fallback
        
        // Set the role based on email
        if ($validated['email'] === $hodEmail) {
            $validated['role'] = 'hod';
            $validated['is_approved'] = true; // Auto-approve HOD
        } else {
            $validated['role'] = 'staff'; // Default role for other users
            $validated['is_approved'] = false; // Other users need approval
        }

        $user = User::create($validated);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($validated)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Login successful!');
        }

        throw ValidationException::withMessages([
            'credentials' => 'Incorrect email or password.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('show.login')->with('success', 'Logged out successfully!');
    }
}