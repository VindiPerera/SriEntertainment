<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $users = User::orderBy('updated_at', 'desc')
            ->select('id', 'name', 'email', 'role_type', 'updated_at')
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'role_type' => 'required|in:Admin,Manager,Cashier',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->banner('User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
            'role_type' => 'required|in:Admin,Manager,Cashier',
            'password'  => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->banner('User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->dangerBanner('You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->banner('User deleted successfully.');
    }
}
