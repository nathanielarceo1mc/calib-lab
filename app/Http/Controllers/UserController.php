<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    protected array $roleOptions = [
        'System Admin',
        'Receiving',
        'TSRF Approver',
        'Technical',
    ];

    public function index()
    {
        // Show all users (including the currently logged-in one)
        $users = User::orderBy('name')->get();

        return view('users.index', [
            'users'       => $users,
            'roleOptions' => $this->roleOptions,
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'roleOptions' => $this->roleOptions,
        ]);
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

            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['string', 'max:255'],
            'groups'          => ['nullable', 'string', 'max:255'],
            'service_types'   => ['nullable', 'string', 'max:255'],

            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        // Build the "name" field from first + last
        $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);

        // Generate a temp password; user will set their own via email link
        $tempPassword = Str::random(16);
        $data['password'] = Hash::make($tempPassword);

        $data['is_active'] = true;
        $data['must_change_password'] = true;

        // Store roles as comma-separated string for now
        if (!empty($data['roles']) && is_array($data['roles'])) {
            $data['roles'] = implode(', ', $data['roles']);
        } else {
            $data['roles'] = null;
        }

        $user = User::create($data);

        // Mark email verified and send only a password reset link to let the user set their password
        $user->forceFill(['email_verified_at' => now()])->save();
        if ($user->email) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully. Password setup link sent.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user'        => $user,
            'roleOptions' => $this->roleOptions,
        ]);
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

            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['string', 'max:255'],
            'groups'          => ['nullable', 'string', 'max:255'],
            'service_types'   => ['nullable', 'string', 'max:255'],

            'email'           => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_active'       => ['nullable', 'boolean'],
            'password'        => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data['name']      = trim($data['first_name'] . ' ' . $data['last_name']);
        $data['is_active'] = $request->boolean('is_active');

        if (!empty($data['roles']) && is_array($data['roles'])) {
            $data['roles'] = implode(', ', $data['roles']);
        } else {
            $data['roles'] = null;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

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
