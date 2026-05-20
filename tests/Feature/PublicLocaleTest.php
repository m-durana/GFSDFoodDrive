<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REL-06: public/token-bearer pages pick en/es via the SetPublicLocale middleware.
 * Staff/Santa pages stay English (not asserted here — covered by absence
 * of the middleware on those route groups).
 */
class PublicLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_defaults_to_english(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Love in Action')
            ->assertSee('How to Help');
    }

    public function test_homepage_renders_spanish_via_lang_query(): void
    {
        $response = $this->get('/?lang=es');
        $response->assertOk();
        $response->assertSee('Amor en acción', false);
        // Cookie set for future visits.
        $this->assertSame('es', $response->getCookie('gfsd_lang')?->getValue());
    }

    public function test_homepage_honors_existing_es_cookie(): void
    {
        $this->withCookie('gfsd_lang', 'es')
            ->get('/')
            ->assertOk()
            ->assertSee('Amor en acción', false);
    }

    public function test_homepage_ignores_unsupported_lang_query(): void
    {
        $this->get('/?lang=fr')
            ->assertOk()
            ->assertSee('Love in Action');
    }

    public function test_homepage_honors_accept_language_header(): void
    {
        $this->withHeader('Accept-Language', 'es-MX, es;q=0.9')
            ->get('/')
            ->assertOk()
            ->assertSee('Amor en acción', false);
    }

    public function test_html_lang_attribute_reflects_locale(): void
    {
        $this->get('/?lang=es')
            ->assertOk()
            ->assertSee('<html lang="es"', false);
    }
}
