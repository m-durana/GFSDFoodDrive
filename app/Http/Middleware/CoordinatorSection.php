<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a Coordinator route group to specific section slugs.
 *
 * Usage in routes/web.php:
 *   Route::middleware(['auth', 'permission:coordinator,santa', 'section:giving-tree,food'])
 *
 * Santa and System Coordinator implicitly pass every check. Other authenticated
 * users must have a `position` whose slug (per User::sectionSlug()) is in the
 * allowed list.
 *
 * Section slugs are extracted from the operator-managed coordinator_positions
 * setting (see SantaController::settings). The conversion strips role-noise
 * suffixes ("Coordinator", "Manager", etc.) and lowercase-slugifies.
 */
class CoordinatorSection
{
    public function handle(Request $request, Closure $next, string ...$allowed): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->hasCoordinatorSection($allowed)) {
            abort(403, 'Your coordinator section does not include this area.');
        }

        return $next($request);
    }
}
