<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * REL-07: deduplicate replayed mutating requests from the offline scanner queue.
 *
 * Clients that drain a queue may POST the same action twice (network retry +
 * post-reconnect drain). We key off `X-Idempotency-Key` and cache the first
 * response keyed by (key, endpoint, user_id). Subsequent hits return the cached
 * body verbatim. Requests without the header pass through unchanged — back-compat
 * with all current online-only callers.
 *
 * Scoping the cache by (endpoint, user_id) means a key reused across endpoints
 * or by a different user is treated as a fresh request, not a replay.
 */
class IdempotentRequest
{
    /** Header name clients send. */
    public const HEADER = 'X-Idempotency-Key';

    /** TTL beyond which a cached response is no longer eligible to short-circuit. */
    public const TTL_HOURS = 24;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->headers->get(self::HEADER);
        if (!$key || !$this->validKey($key)) {
            return $next($request);
        }

        $endpoint = $this->endpointFor($request);
        $userId = $request->user()?->id;

        // Inside a transaction so two near-simultaneous requests with the same
        // key serialize cleanly. The lockForUpdate forces a write-lock on the
        // primary key row — if the row already exists, the second request
        // blocks until the first commits, then reads the cached response.
        return DB::transaction(function () use ($request, $next, $key, $endpoint, $userId) {
            $row = DB::table('idempotency_keys')
                ->where('key', $key)
                ->where('endpoint', $endpoint)
                ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
                ->lockForUpdate()
                ->first();

            if ($row) {
                $body = is_string($row->response_body)
                    ? json_decode($row->response_body, true)
                    : (array) $row->response_body;

                return new JsonResponse($body, (int) $row->response_status, [
                    'X-Idempotent-Replay' => '1',
                ]);
            }

            // Render the controller response — including validation / HTTP
            // exceptions — so we can cache 4xx outcomes too. The offline-queue
            // semantics demand that any deterministic 4xx be replayable, not
            // re-tried infinitely.
            try {
                $response = $next($request);
            } catch (Throwable $e) {
                $response = app(ExceptionHandler::class)->render($request, $e);
            }

            // Only cache structured JSON responses. Streams / files / redirects
            // are not safe to replay.
            $contentType = $response->headers->get('Content-Type', '');
            $shouldCache = $response instanceof JsonResponse
                || str_contains($contentType, 'application/json');

            if ($shouldCache && $response->getStatusCode() < 500) {
                $body = $response->getContent();
                DB::table('idempotency_keys')->insert([
                    'key' => $key,
                    'endpoint' => $endpoint,
                    'user_id' => $userId,
                    'response_status' => $response->getStatusCode(),
                    'response_body' => $body,
                    'created_at' => now(),
                ]);
            }

            return $response;
        });
    }

    /** UUIDv4 or any 16-64 char hex/dash token. Cheap sanity check. */
    private function validKey(string $key): bool
    {
        return strlen($key) >= 16 && strlen($key) <= 64
            && preg_match('/^[A-Za-z0-9\-_]+$/', $key) === 1;
    }

    /**
     * Use the route name when available — survives parameter changes (e.g.
     * the same logical endpoint called against /api/packing/1/item/1/pack
     * vs /api/packing/2/item/3/pack collapses to one endpoint name).
     */
    private function endpointFor(Request $request): string
    {
        $name = $request->route()?->getName();
        if ($name) {
            return substr($name, 0, 128);
        }
        return substr($request->method() . ' ' . $request->path(), 0, 128);
    }
}
