<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()->when($request->string('search')->trim()->value(), function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        })->when($request->role, fn ($query, $role) => $query->where('role', $role))->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', ['users' => $users, 'roles' => User::ROLES]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['role' => ['required', 'in:'.implode(',', User::ROLES)]]);
        abort_if($request->user()->is($user) && $validated['role'] !== 'admin', 422, 'You cannot remove your own admin role.');
        $user->update($validated);
        ActivityLogger::record('updated_user_role', "{$request->user()->name} changed {$user->email}'s role to {$validated['role']}.", $user, $request);

        return back()->with('success', 'User role updated.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot deactivate your own account.');
        $user->update(['is_active' => ! $user->is_active]);
        ActivityLogger::record('updated_user_status', "{$request->user()->name} changed {$user->email}'s active status.", $user, $request);

        return back()->with('success', 'User account status updated.');
    }
}