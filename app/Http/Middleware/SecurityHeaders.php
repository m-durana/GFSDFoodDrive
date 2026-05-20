<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REL-28: Append HSTS + a pragmatic Content-Security-Policy to every response.
 *
 * The CSP is intentionally permissive about external CDNs we actually depend on
 * (Tailwind CDN on token-bearer pages, Leaflet tiles, fonts.bunny.net, jsdelivr
 * for chart.js). Tightening further is post-ship — would require migrating the
 * standalone CDN pages to Vite builds.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS — only on HTTPS so local dev keeps working.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        // Hardening headers that don't carry CSP's complexity.
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()', false);

        // Skip CSP on PDF / attachment responses (dompdf inlines its own stuff).
        $ct = (string) $response->headers->get('Content-Type', '');
        if (str_contains($ct, 'application/pdf') || $response->headers->get('Content-Disposition') !== null) {
            return $response;
        }

        $csp = implode('; ', [
            "default-src 'self'",
            // 'unsafe-inline' for Tailwind CDN config + Alpine x-data + Leaflet popups.
            // 'unsafe-eval' for Alpine.js x-show evaluator.
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://unpkg.com",
            // Tailwind/Vite emit inline <style> on first paint; daisyUI also injects.
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://unpkg.com",
            "font-src 'self' https://fonts.bunny.net data:",
            // Leaflet tiles + Sentry beacon + image CDNs.
            "img-src 'self' data: blob: https://*.tile.openstreetmap.org https://www.facebook.com https://*.ingest.sentry.io",
            "connect-src 'self' https://*.ingest.sentry.io https://api.openrouteservice.org https://nominatim.openstreetmap.org",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp, false);

        return $response;
    }
}
