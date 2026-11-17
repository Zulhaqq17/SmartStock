<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only lets users with one of the listed roles through, otherwise 403.
 * Used in routes/web.php, e.g. EnsureRole::class.':Manager,Warehouse Staff'
 * Team leader owns this file.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($request->user() && ! $request->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if (! $user || ! $user->hasRole(...$roles)) {
            abort(403, 'You do not have permission to view this page.');
        }

        return $next($request);
    }
}
