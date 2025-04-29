<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

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
        // Check if HOD already exists
        $hodExists = User::where('role', 'hod')->exists();
        
        return view('users.create', [
            'hodExists' => $hodExists,
            'hodEmail' => Config::get('roles.hod_email', 'hod@example.com')
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
                Rule::in(['staff', 'manager', 'hod']),
            ],
        ]);

        // Check if trying to create HOD but it's not the designated email
        $hodEmail = Config::get('roles.hod_email', 'hod@example.com');
        if ($data['role'] === 'hod' && $data['email'] !== $hodEmail) {
            return redirect()->back()->withErrors([
                'email' => "Only the designated email ({$hodEmail}) can be assigned the HOD role."
            ])->withInput();
        }
        
        // Check if HOD already exists when trying to create another HOD
        if ($data['role'] === 'hod' && User::where('role', 'hod')->exists()) {
            return redirect()->back()->withErrors([
                'role' => 'HOD role already assigned to another user. There can only be one HOD.'
            ])->withInput();
        }

        // Hash the password
        $data['password'] = bcrypt($data['password']);
        
        // Set default approval status
        $data['is_approved'] = true;

        $newUser = User::create($data);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user) 
    {
        // Check if HOD already exists (excluding current user)
        $hodExists = User::where('role', 'hod')
                     ->where('id', '!=', $user->id)
                     ->exists();
                     
        return view('users.edit', [
            'user' => $user,
            'hodExists' => $hodExists,
            'hodEmail' => Config::get('roles.hod_email', 'hod@example.com')
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
                Rule::in(['staff', 'manager', 'hod']),
            ],
            'is_approved' => 'sometimes|boolean',
        ]);

        // Check if trying to change to HOD but it's not the designated email
        $hodEmail = Config::get('roles.hod_email', 'hod@example.com');
        if ($data['role'] === 'hod' && $data['email'] !== $hodEmail) {
            return redirect()->back()->withErrors([
                'role' => "Only the designated email ({$hodEmail}) can be assigned the HOD role."
            ])->withInput();
        }
        
        // Check if HOD already exists when trying to change role to HOD
        if ($data['role'] === 'hod' && $user->role !== 'hod' && User::where('role', 'hod')->exists()) {
            return redirect()->back()->withErrors([
                'role' => 'HOD role already assigned to another user. There can only be one HOD.'
            ])->withInput();
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
}