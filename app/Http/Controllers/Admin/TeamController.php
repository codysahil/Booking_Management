<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        $team = User::latest()->get();

        return view('admin.team.index', compact('team'));
    }

    public function create()
    {
        return view('admin.team.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', array_keys(User::ROLES)),
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.team.index')->with('success', 'Team member added.');
    }

    public function edit(User $team)
    {
        return view('admin.team.edit', ['member' => $team]);
    }

    public function update(Request $request, User $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($team->id)],
            'role' => 'required|string|in:' . implode(',', array_keys(User::ROLES)),
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $team->update($validated);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated.');
    }

    public function destroy(User $team)
    {
        if ($team->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot remove your own account.']);
        }

        $team->delete();

        return back()->with('success', 'Team member removed.');
    }
}
