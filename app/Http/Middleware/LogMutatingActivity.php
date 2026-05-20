<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Writes an audit-log entry for every state-changing request made by an
 * authenticated user. Sudoer (is_sudoer=true) actions are tagged separately
 * so they're easy to filter for in a "what did this deputy do?" review.
 *
 * Read-only requests (GET / HEAD / OPTIONS) are skipped to keep volume sane.
 * A small allow-list of high-frequency mutating endpoints (driver location
 * pings, e2e reset) is also skipped — they'd otherwise dominate the log.
 *
 * Sits on the global `web` stack.
 */
class LogMutatingActivity
{
    private const MUTATING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Route names that fire too often to be worth auditing individually.
     */
    private const SKIP_ROUTES = [
        'delivery.updateDriverLocation',
        'delivery.markHeading',
        'delivery.routeData',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (! $user) {
            return $response;
        }

        if (! in_array($request->method(), self::MUTATING_METHODS, true)) {
            return $response;
        }

        $routeName = optional($request->route())->getName();
        if ($routeName && in_array($routeName, self::SKIP_ROUTES, true)) {
            return $response;
        }

        try {
            $isSudo = $user->isSudoer();
            AuditLog::create([
                'auditable_type' => $isSudo ? 'sudo_action' : 'mutation',
                'auditable_id' => 0,
                'actor_id' => $user->id,
                'action' => ($isSudo ? 'sudo_' : '').strtolower($request->method()),
                'before' => null,
                'after' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'route' => $routeName,
                    'status' => $response->getStatusCode(),
                    'is_sudoer' => $isSudo,
                ],
                'ip' => $request->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the request. Errors surface in Sentry.
            report($e);
        }

        return $response;
    }
}
