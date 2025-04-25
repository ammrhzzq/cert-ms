<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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
        return view('users.create');
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
            'role' => 'required|string|in:staff,manager,hod',
        ]);

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
        return view('users.edit', ['user' => $user]);
    }

    /**
     * Update the specified user in the database.
     */
    public function update(Request $request, User $user) 
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,'.$user->id,
            'role' => 'required|string|in:staff,manager,hod',
            'is_approved' => 'sometimes|boolean',
        ]);

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