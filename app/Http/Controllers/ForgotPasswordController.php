<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use App\Mail\SendOtpMail;

class ForgotPasswordController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.auth', [
            'activeTab' => 'forgot-password'
        ]);
    }

    public function showOtpForm()
    {
        return view('auth.auth', [
            'activeTab' => 'verify-otp'
        ]);
    }

    public function showResetForm()
    {
        return view('auth.auth', [
            'activeTab' => 'reset-password'
        ]);
    }

    public function sendOtp(Request $request)
    {

        $request->validate(['email' => 'required|email|exists:users,email']);

        $otp = rand(100000, 999999);

        Session::put('otp', $otp);
        Session::put('reset_email', $request->email);
        Session::put('otp_expires_at', Carbon::now()->addMinutes(5));

        Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
            $message->to($request->email)->subject("Your Password Reset OTP Code");
        });

        Mail::to($request->email)->send(new SendOtpMail($otp));

        return redirect()->route('password.otp')->with('success', 'OTP sent to your email.');
    }


    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        if ($request->otp == Session::get('otp')) {
            Session::forget('otp_attempts');
            return redirect()->route('password.reset.form');
        }

        Session::put('otp_attempts', 0); // In sendOtp()

        // In verifyOtp()
        $attempts = Session::get('otp_attempts', 0);

        if ($attempts >= 3) {
            return redirect()->route('password.request')->withErrors(['otp' => 'Too many attempts. Please request a new OTP.']);
        }

        if ($request->otp != Session::get('otp')) {
            Session::put('otp_attempts', $attempts + 1);
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        if (Carbon::now()->greaterThan(Session::get('otp_expires_at'))) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one']);
        }

        return back()->withErrors(['otp' => 'Invalid OTP code.']);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', Session::get('reset_email'))->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Session::forget(['otp', 'reset_email']);

        return redirect()->route('show.login')->with('success', 'Password reset successfully.');
    }
}
