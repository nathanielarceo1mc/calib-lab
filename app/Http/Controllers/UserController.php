<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Show all users (including the currently logged-in one)
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'      => ['required', 'string', 'max:255'],
            'middle_name'     => ['nullable', 'string', 'max:255'],
            'middle_initial'  => ['nullable', 'string', 'max:10'],
            'last_name'       => ['required', 'string', 'max:255'],

            'employee_id'     => ['nullable', 'string', 'max:50'],
            'position'        => ['nullable', 'string', 'max:255'],
            'birth_date'      => ['nullable', 'date'],
            'gender'          => ['nullable', 'string', 'max:20'],
            'marital_status'  => ['nullable', 'string', 'max:50'],
            'home_address'    => ['nullable', 'string', 'max:255'],
            'home_phone'      => ['nullable', 'string', 'max:50'],
            'mobile'          => ['nullable', 'string', 'max:50'],
            'tin'             => ['nullable', 'string', 'max:50'],

            'roles'           => ['nullable', 'string', 'max:255'],
            'groups'          => ['nullable', 'string', 'max:255'],
            'service_types'   => ['nullable', 'string', 'max:255'],

            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        // Build the "name" field from first + last
        $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);

        // Temporary random password
        $data['password'] = Hash::make(Str::random(12));

        $data['is_active'] = true;

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name'      => ['required', 'string', 'max:255'],
            'middle_name'     => ['nullable', 'string', 'max:255'],
            'middle_initial'  => ['nullable', 'string', 'max:10'],
            'last_name'       => ['required', 'string', 'max:255'],

            'employee_id'     => ['nullable', 'string', 'max:50'],
            'position'        => ['nullable', 'string', 'max:255'],
            'birth_date'      => ['nullable', 'date'],
            'gender'          => ['nullable', 'string', 'max:20'],
            'marital_status'  => ['nullable', 'string', 'max:50'],
            'home_address'    => ['nullable', 'string', 'max:255'],
            'home_phone'      => ['nullable', 'string', 'max:50'],
            'mobile'          => ['nullable', 'string', 'max:50'],
            'tin'             => ['nullable', 'string', 'max:50'],

            'roles'           => ['nullable', 'string', 'max:255'],
            'groups'          => ['nullable', 'string', 'max:255'],
            'service_types'   => ['nullable', 'string', 'max:255'],

            'email'           => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_active'       => ['nullable', 'boolean'],
        ]);

        $data['name']      = trim($data['first_name'] . ' ' . $data['last_name']);
        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
