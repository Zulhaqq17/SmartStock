<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * MODULE 1: User account management (Manager only, enforced in routes/web.php)
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $users = User::with('role')
            ->when($request->string('search')->trim()->toString(), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")))
            ->when($request->integer('role_id'), fn ($q, $r) => $q->where('role_id', $r))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', ['roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $data['is_active'] = true;

        User::create($data); // password hashed by the model cast

        return redirect()->route('users.index')->with('success', 'User account created.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user, 'roles' => Role::orderBy('id')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $roleChanged = (int) $data['role_id'] !== (int) $user->role_id;
        $deactivating = $user->is_active && ! $data['is_active'];

        if ($user->is(auth()->user()) && ($roleChanged || $deactivating)) {
            return back()->withInput()->with('error', 'You cannot change your own role or deactivate your own account.');
        }
        if (($roleChanged || $deactivating) && $this->isLastActiveManager($user)) {
            return back()->withInput()->with('error', 'This is the last active Manager. Assign another Manager first.');
        }

        if (empty($data['password'])) {
            unset($data['password']); // blank = keep current password
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        if ($user->is_active && $this->isLastActiveManager($user)) {
            return back()->with('error', 'This is the last active Manager and cannot be deactivated.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    private function isLastActiveManager(User $user): bool
    {
        if (! $user->is_active || ! $user->hasRole(Role::MANAGER)) {
            return false;
        }

        return ! User::where('id', '!=', $user->id)
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->where('role_name', Role::MANAGER))
            ->exists();
    }
}
