<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index() 
    {
        $users = User::all();
        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create() 
    {
        // Check if Scheme Head already exists
        $schemeHeadExists = User::where('role', 'scheme_head')->exists();
        
        // Check if Administrator already exists
        $adminExists = User::where('role', 'administrator')->exists();
        
        return view('users.create', [
            'schemeHeadExists' => $schemeHeadExists,
            'adminExists' => $adminExists,
            'schemeHeadEmail' => Config::get('roles.scheme_head_email', 'schemehead@cybersecurity.my'),
            'adminEmail' => Config::get('roles.admin_email', 'admin@cybersecurity.my')
        ]);
    }

    /**
     * Store a newly created user in the database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => [
                'required',
                'string',
                Rule::in(['certificate_admin', 'scheme_manager', 'scheme_head', 'administrator']),
            ],
        ]);

        // Get designated emails
        $schemeHeadEmail = Config::get('roles.scheme_head_email', 'schemehead@cybersecurity.my');
        $adminEmail = Config::get('roles.admin_email', 'admin@cybersecurity.my');

        // Check if trying to create Scheme Head but it's not the designated email
        if ($data['role'] === 'scheme_head' && $data['email'] !== $schemeHeadEmail) {
            return redirect()->back()->withErrors([
                'email' => "Only the designated email ({$schemeHeadEmail}) can be assigned the Scheme Head role."
            ])->withInput();
        }

        // Check if trying to create Administrator but it's not the designated email
        if ($data['role'] === 'administrator' && $data['email'] !== $adminEmail) {
            return redirect()->back()->withErrors([
                'email' => "Only the designated email ({$adminEmail}) can be assigned the Administrator role."
            ])->withInput();
        }
        
        // Check if Scheme Head already exists when trying to create another Scheme Head
        if ($data['role'] === 'scheme_head' && User::where('role', 'scheme_head')->exists()) {
            return redirect()->back()->withErrors([
                'role' => 'Scheme Head role already assigned to another user. There can only be one Scheme Head.'
            ])->withInput();
        }

        // Check if Administrator already exists when trying to create another Administrator
        if ($data['role'] === 'administrator' && User::where('role', 'administrator')->exists()) {
            return redirect()->back()->withErrors([
                'role' => 'Administrator role already assigned to another user. There can only be one Administrator.'
            ])->withInput();
        }

        // Hash the password
        $data['password'] = bcrypt($data['password']);
        
        // Set default approval status
        $data['is_approved'] = true;

        // Auto-verify email for Scheme Head and Administrator
        if (in_array($data['role'], ['scheme_head', 'administrator'])) {
            $data['email_verified_at'] = now();
        }

        $newUser = User::create($data);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user) 
    {
        // Check if Scheme Head already exists (excluding current user)
        $schemeHeadExists = User::where('role', 'scheme_head')
                     ->where('id', '!=', $user->id)
                     ->exists();

        // Check if Administrator already exists (excluding current user)
        $adminExists = User::where('role', 'administrator')
                      ->where('id', '!=', $user->id)
                      ->exists();
                     
        return view('users.edit', [
            'user' => $user,
            'schemeHeadExists' => $schemeHeadExists,
            'adminExists' => $adminExists,
            'schemeHeadEmail' => Config::get('roles.scheme_head_email', 'hod@cybersecurity.my'),
            'adminEmail' => Config::get('roles.admin_email', 'admin@cybersecurity.my')
        ]);
    }

    /**
     * Update the specified user in the database.
     */
    public function update(Request $request, User $user) 
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,'.$user->id,
            'role' => [
                'required',
                'string',
                Rule::in(['certificate_admin', 'scheme_manager', 'scheme_head', 'admin']),
            ],
            'is_approved' => 'sometimes|boolean',
        ]);

        // Get designated emails
        $schemeHeadEmail = Config::get('roles.scheme_head_email', 'hod@cybersecurity.my');
        $adminEmail = Config::get('roles.admin_email', 'admin@cybersecurity.my');

        // Check if trying to change to Scheme Head but it's not the designated email
        if ($data['role'] === 'scheme_head' && $data['email'] !== $schemeHeadEmail) {
            return redirect()->back()->withErrors([
                'role' => "Only the designated email ({$schemeHeadEmail}) can be assigned the Scheme Head role."
            ])->withInput();
        }

        // Check if trying to change to Administrator but it's not the designated email
        if ($data['role'] === 'admin' && $data['email'] !== $adminEmail) {
            return redirect()->back()->withErrors([
                'role' => "Only the designated email ({$adminEmail}) can be assigned the Administrator role."
            ])->withInput();
        }
        
        // Check if Scheme Head already exists when trying to change role to Scheme Head
        if ($data['role'] === 'scheme_head' && $user->role !== 'scheme_head' && User::where('role', 'scheme_head')->exists()) {
            return redirect()->back()->withErrors([
                'role' => 'Scheme Head role already assigned to another user. There can only be one Scheme Head.'
            ])->withInput();
        }

        // Check if Administrator already exists when trying to change role to Administrator
        if ($data['role'] === 'administrator' && $user->role !== 'administrator' && User::where('role', 'administrator')->exists()) {
            return redirect()->back()->withErrors([
                'role' => 'Administrator role already assigned to another user. There can only be one Administrator.'
            ])->withInput();
        }

        // Auto-verify email when changing to Scheme Head or Administrator role
        if (in_array($data['role'], ['scheme_head', 'administrator']) && !in_array($user->role, ['scheme_head', 'administrator'])) {
            $data['email_verified_at'] = now();
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from the database.
     */
    public function destroy(User $user) 
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Show the form for resetting user password (Admin only)
     */
    public function showResetPasswordForm(User $user)
    {
        return view('users.reset-password', compact('user'));
    }

    /**
     * Reset user password (Admin only)
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
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

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('users.index')->with('success', 'Password reset successfully for ' . $user->name);
    }
}