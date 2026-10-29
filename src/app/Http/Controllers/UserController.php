<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * MODULE 1 (continued): Manager-only user management.
 * Owner: TODO (name)
 */
class UserController extends Controller
{
    /** TODO: list users with their role; activate/deactivate buttons. */
    public function index(): View
    {
        return view('users.index');
    }

    /** TODO: form (name, email, role dropdown, temporary password). */
    public function create(): View
    {
        return view('users.create');
    }

    /** TODO: validate, create the user (password is hashed automatically by the User model). */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('error', 'TODO: not implemented yet.');
    }
}
