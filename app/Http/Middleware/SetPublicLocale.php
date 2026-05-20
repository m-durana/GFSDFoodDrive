<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REL-06: pick the request locale for public / token-bearer pages.
 *
 * Resolution order:
 *   1. ?lang=es / ?lang=en query string — sets the cookie + locale (for the
 *      EN/ES toggle link on the homepage).
 *   2. `gfsd_lang` cookie — sticks across navigation.
 *   3. The family's `preferred_language` when the request is a token-bearer
 *      view bound to a family (passed via the route's `family` parameter or
 *      resolved upstream).
 *   4. `Accept-Language` header — first-time visitor's browser preference.
 *   5. Fallback to `en`.
 *
 * Only `en` and `es` are supported today. Anything else falls back to `en`.
 * Staff/Santa pages do NOT use this middleware — they stay English.
 */
class SetPublicLocale
{
    public const SUPPORTED = ['en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);
        app()->setLocale($locale);

        $response = $next($request);

        // If the request explicitly asked for a locale via ?lang=, pin it.
        if ($request->query('lang') && in_array($request->query('lang'), self::SUPPORTED, true)) {
            $cookie = cookie('gfsd_lang', $locale, 60 * 24 * 365);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    private function resolve(Request $request): string
    {
        $query = $request->query('lang');
        if ($query && in_array($query, self::SUPPORTED, true)) {
            return $query;
        }

        $cookie = $request->cookie('gfsd_lang');
        if ($cookie && in_array($cookie, self::SUPPORTED, true)) {
            return $cookie;
        }

        // Token-bearer routes may resolve a Family upstream; honour it if so.
        $family = $request->route('family');
        if (is_object($family) && property_exists($family, 'preferred_language')) {
            $pref = strtolower((string) ($family->preferred_language ?? ''));
            if (in_array($pref, self::SUPPORTED, true)) {
                return $pref;
            }
        }

        $accept = strtolower((string) $request->getPreferredLanguage(self::SUPPORTED));
        if (in_array($accept, self::SUPPORTED, true)) {
            return $accept;
        }

        return 'en';
    }
}
