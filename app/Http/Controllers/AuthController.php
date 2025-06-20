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
        $hodEmail = Config::get('app.hod_email', 'hod@cybersecurity.my'); // Default value as fallback
        
        // Set the role based on email
        if ($validated['email'] === $hodEmail) {
            $validated['role'] = 'hod';
            $validated['is_approved'] = true; // Auto-approve HOD
        } else {
            $validated['role'] = 'staff'; // Default role for other users
            $validated['is_approved'] = false; // Other users need approval
        }

        $user = User::create($validated);

        // Only login if user is approved (HOD is auto-approved)
        if ($validated['is_approved']) {
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Registration successful!');
        } else {
            return redirect()->route('show.login')->with('success', 'Registration successful! Please wait for HoD approval before logging in.');
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // First, check if credentials are correct
        if (Auth::attempt($validated)) {
            $user = Auth::user();
            
            // Check if user is approved
            if (!$user->is_approved) {
                Auth::logout(); // Log them out immediately
                
                throw ValidationException::withMessages([
                    'credentials' => 'Your account is pending approval. Please contact your HoD.',
                ]);
            }
            
            // User is approved, proceed with login
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