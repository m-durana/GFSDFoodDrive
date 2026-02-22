<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request by checking role names.
     *
     * Uses Spatie hasAnyRole() when available, otherwise falls back to
     * legacy integer permission column.
     *
     * Usage in routes: ->middleware('permission:family,santa')
     *
     * @param  string  ...$roles  Allowed role names (e.g., 'family', 'santa')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // Check Spatie roles if the trait is loaded
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Fallback: check legacy integer permission column
        $roleToPermission = [
            'self_service' => 6,
            'family' => 7,
            'coordinator' => 8,
            'santa' => 9,
        ];

        foreach ($roles as $role) {
            if (isset($roleToPermission[$role]) && $user->permission === $roleToPermission[$role]) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. You do not have permission to access this page.');
    }
}
