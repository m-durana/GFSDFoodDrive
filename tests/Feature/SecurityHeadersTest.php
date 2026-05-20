<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_carries_csp_and_hardening_headers(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_hsts_emitted_only_over_https(): void
    {
        // Local dev (http) — no HSTS.
        $response = $this->get('/');
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_csp_skipped_for_pdf_responses(): void
    {
        // No straightforward PDF endpoint without auth + section gates — just sanity-check
        // that the middleware logic short-circuits when Content-Type is pdf. We do this
        // via the unit under test directly.
        $request = \Illuminate\Http\Request::create('/');
        $next = fn () => (new \Symfony\Component\HttpFoundation\Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]));
        $response = (new \App\Http\Middleware\SecurityHeaders())->handle($request, $next);
        $this->assertNull($response->headers->get('Content-Security-Policy'));
    }
}
