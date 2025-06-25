<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

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
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters long.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.mixed_case' => 'Password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character.',
            'password.uncompromised' => 'This password has been compromised in data breaches. Please choose a different password.',
        ]);

        // Hash the password
        $validated['password'] = bcrypt($validated['password']);
        
        // Check if the email is the designated Scheme Head or Administrator email
        $schemeHeadEmail = env('SCHEME_HEAD_EMAIL', 'schemehead@cybersecurity.my');
        $adminEmail = env('ADMIN_EMAIL', 'admin@cybersecurity.my');
        
        // Get email domain
        $emailDomain = substr(strrchr($validated['email'], '@'), 1);
        
        // Set the role based on email
        if ($validated['email'] === $schemeHeadEmail) {
            $validated['role'] = 'scheme_head';
            $validated['is_approved'] = true; // Auto-approve Scheme Head
            $validated['email_verified_at'] = now(); // Auto-verify Scheme Head email
        } elseif ($validated['email'] === $adminEmail) {
            $validated['role'] = 'admin';
            $validated['is_approved'] = true; // Auto-approve Administrator
            $validated['email_verified_at'] = now(); // Auto-verify Administrator email
        } else {
            $validated['role'] = 'certificate_admin';
            
            // Auto-approve Gmail users, others need Scheme Head approval
            // TODO: Remove Gmail auto-approval once domain email is configured
            if ($emailDomain === 'gmail.com') {
                $validated['is_approved'] = true; // Auto-approve Gmail users
            } else {
                $validated['is_approved'] = false; // Others need Scheme Head approval
            }
        }

        $user = User::create($validated);

        // Only send verification email for certificate admin users (skip Scheme Head and Administrator)
        if (!in_array($user->role, ['scheme_head', 'admin'])) {
            // Generate and send email verification token
            $user->generateEmailVerificationToken();
            
            try {
                $user->notify(new EmailVerificationNotification($user->email_verification_token));
                Log::info('Email verification sent to: ' . $user->email);
                
                return redirect()->route('show.verify.email', ['user' => $user->id])
                    ->with('success', 'Registration successful! Please check your email for verification code.');
            } catch (\Exception $e) {
                Log::error('Failed to send verification email: ' . $e->getMessage());
                
                return redirect()->route('show.verify.email', ['user' => $user->id])
                    ->with('error', 'Registration successful but failed to send verification email. Please contact support.');
            }
        } else {
            // Scheme Head or Administrator registration complete - redirect to login
            $roleTitle = $user->role === 'scheme_head' ? 'Scheme Head' : 'Admin';
            return redirect()->route('show.login')
                ->with('success', $roleTitle . ' registration successful! You can now login directly.');
        }
    }

    public function showVerifyEmail(User $user)
    {
        // Check if user already verified
        if ($user->email_verified_at !== null) {
            return redirect()->route('show.login')
                ->with('success', 'Email already verified. You can now login.');
        }

        return view('auth.auth', [
            'activeTab' => 'verify-email',
            'user' => $user
        ]);
    }

    public function verifyEmail(Request $request, User $user)
    {
        $validated = $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        // Check if user already verified
        if ($user->email_verified_at !== null) {
            return redirect()->route('show.login')
                ->with('success', 'Email already verified. You can now login.');
        }

        // Validate the token
        if (!$user->isValidEmailVerificationToken($validated['verification_code'])) {
            $user->incrementVerificationAttempts();
            
            // Check if max attempts reached
            if ($user->email_verification_attempts >= 3) {
                return back()->withErrors([
                    'verification_code' => 'Too many failed attempts. Please request a new verification code.'
                ]);
            }

            return back()->withErrors([
                'verification_code' => 'Invalid or expired verification code.'
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // OPTION 1: Auto-login after verification (if already approved)
        if ($user->is_approved) {
            Auth::login($user);
            return redirect()->route('dashboard')
                ->with('success', 'Email verified successfully! Welcome to your dashboard.');
        }
        
        // OPTION 2: Still need approval
        return redirect()->route('show.login')
            ->with('success', 'Email verified successfully! Please wait for approval before logging in.');
    }

    public function resendVerificationCode(User $user)
    {
        // Check if user already verified
        if ($user->email_verified_at !== null) {
            return redirect()->route('show.login')
                ->with('success', 'Email already verified. You can now login.');
        }

        // Rate limiting - allow resend only after 2 minutes
        if ($user->email_verification_expires_at && 
            $user->email_verification_expires_at->diffInMinutes(Carbon::now()) < 8) {
            return back()->withErrors([
                'resend' => 'Please wait before requesting another verification code.'
            ]);
        }

        // Generate new token and send email
        $user->generateEmailVerificationToken();
        
        try {
            $user->notify(new EmailVerificationNotification($user->email_verification_token));
            Log::info('Verification code resent to: ' . $user->email);
            return back()->with('success', 'New verification code sent to your email.');
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            return back()->withErrors([
                'resend' => 'Failed to send verification code. Please try again later.'
            ]);
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
            
            // Skip email verification check for Scheme Head and Administrator
            if (!in_array($user->role, ['scheme_head', 'admin'])) {
                // Check if email is verified for certificate admin users
                if ($user->email_verified_at === null) {
                    Auth::logout();
                    
                    // Redirect to verification page
                    return redirect()->route('show.verify.email', ['user' => $user->id])
                        ->withErrors(['credentials' => 'Please verify your email address first.']);
                }
            }
            
            // Check if user is approved
            if (!$user->is_approved) {
                Auth::logout();
                
                throw ValidationException::withMessages([
                    'credentials' => 'Your account is pending approval. Please contact your administrator.',
                ]);
            }
            
            // User is verified and approved (or is Scheme Head/Administrator), proceed with login
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