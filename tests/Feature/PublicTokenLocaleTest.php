<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\ShoppingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * REL-06: Spanish wrapping for token-bearer / public surfaces.
 *
 * These tests assert that ?lang=es renders a known Spanish string on each
 * public-locale view. They don't try to be exhaustive — they catch regressions
 * where a view stops honouring app()->getLocale() at all.
 */
class PublicTokenLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        Setting::clearCache();
    }

    public function test_adopt_index_renders_spanish_with_lang_query(): void
    {
        Setting::set('adopt_a_tag_enabled', '1');
        Setting::clearCache();

        $response = $this->get(route('adopt.index', ['lang' => 'es']));
        $response->assertOk();
        $response->assertSee('Adopta una etiqueta', false);
    }

    public function test_adopt_disabled_page_renders_spanish(): void
    {
        Setting::set('adopt_a_tag_enabled', '0');
        Setting::clearCache();

        $response = $this->get(route('adopt.index', ['lang' => 'es']));
        $response->assertOk();
        $response->assertSee('La adopción en línea está deshabilitada actualmente', false);
    }

    public function test_self_service_closed_renders_spanish(): void
    {
        Setting::set('self_registration_enabled', '0');
        Setting::clearCache();

        $response = $this->get(route('self-service.create', ['lang' => 'es']));
        $response->assertOk();
        $response->assertSee('El registro de familias está cerrado actualmente', false);
    }

    public function test_self_service_form_renders_spanish_when_enabled(): void
    {
        Setting::set('self_registration_enabled', '1');
        Setting::clearCache();

        $response = $this->get(route('self-service.create', ['lang' => 'es']));
        $response->assertOk();
        $response->assertSee('Registro de familia', false);
        $response->assertSee('Apellido de la familia', false);
        $response->assertSee('Idioma preferido', false);
    }

    public function test_family_status_page_picks_spanish_from_family_preference(): void
    {
        Setting::set('family_status_enabled', '1');
        $family = Family::create([
            'family_name' => 'Lopez', 'family_number' => 77,
            'number_of_family_members' => 4, 'number_of_adults' => 2,
            'number_of_children' => 2, 'phone1' => '555-9999', 'address' => '77 Calle',
            'status_token' => Str::random(32),
            'preferred_language' => 'Spanish',
        ]);

        $response = $this->get(route('family.status', $family->status_token));
        $response->assertOk();
        $response->assertSee('Hola, familia Lopez!', false);
        $response->assertSee('Tu estado', false);
        $response->assertSee('Registrada', false);
    }

    public function test_family_status_page_honors_lang_query_over_family_preference(): void
    {
        Setting::set('family_status_enabled', '1');
        $family = Family::create([
            'family_name' => 'Lopez', 'family_number' => 77,
            'number_of_family_members' => 4, 'number_of_adults' => 2,
            'number_of_children' => 2, 'phone1' => '555-9999', 'address' => '77 Calle',
            'status_token' => Str::random(32),
            'preferred_language' => 'Spanish',
        ]);

        $response = $this->get(route('family.status', $family->status_token).'?lang=en');
        $response->assertOk();
        $response->assertSee('Hello, Lopez family!');
        $response->assertSee('Your Status');
    }

    public function test_shopping_checklist_renders_spanish(): void
    {
        $family = Family::create([
            'family_name' => 'Garcia', 'family_number' => 88,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-8888', 'address' => '88 Way',
        ]);

        $response = $this->get('/shopping/'.$family->family_number.'?lang=es');
        $response->assertOk();
        $response->assertSee('Lista de compras', false);
    }
}
