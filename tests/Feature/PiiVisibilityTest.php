<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the PII redaction policy from docs/PROJECT_OVERVIEW.md §3.2.
 *
 * Only Santa and System Coordinator may see real names/addresses/phones.
 * Advisors may see PII for families *they themselves entered*. Everyone
 * else (regular Coordinators, Advisors looking at foreign families,
 * unauthenticated token-bearers) must see family numbers only.
 */
class PiiVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $coordinator;
    private User $systemCoordinator;
    private User $ownerAdvisor;
    private User $otherAdvisor;
    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Spatie roles (system_coordinator is Spatie-only).
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->santa = $this->makeUser('santa_pii', 9, 'santa');
        $this->coordinator = $this->makeUser('coord_pii', 8, 'coordinator');
        $this->systemCoordinator = $this->makeUser('syscoord_pii', 8, 'system_coordinator');
        $this->ownerAdvisor = $this->makeUser('owner_pii', 7, 'family');
        $this->otherAdvisor = $this->makeUser('other_pii', 7, 'family');

        $this->family = Family::create([
            'user_id' => $this->ownerAdvisor->id,
            'family_name' => 'TopSecretFamilyName',
            'family_number' => 42,
            'address' => '123 Hidden Lane',
            'phone1' => '555-555-5555',
            'female_adults' => 1, 'male_adults' => 1, 'other_adults' => 0,
            'infants' => 0, 'young_children' => 0, 'children_count' => 0,
            'tweens' => 0, 'teenagers' => 0,
            'number_of_adults' => 2, 'number_of_children' => 0, 'number_of_family_members' => 2,
        ]);
    }

    private function makeUser(string $username, int $permission, ?string $role): User
    {
        $user = User::create([
            'username' => $username,
            'first_name' => ucfirst($username),
            'last_name' => 'Test',
            'password' => 'password',
            'permission' => $permission,
        ]);

        if ($role && method_exists($user, 'assignRole')) {
            try {
                $user->assignRole($role);
            } catch (\Throwable $e) {
                // Spatie not configured; fall back to permission integer.
            }
        }
        $user->refresh();
        return $user;
    }

    public function test_can_see_pii_helper_only_true_for_santa_and_system_coordinator(): void
    {
        $this->assertTrue($this->santa->canSeePii());
        $this->assertTrue($this->systemCoordinator->canSeePii());
        $this->assertFalse($this->coordinator->canSeePii());
        $this->assertFalse($this->ownerAdvisor->canSeePii());
        $this->assertFalse($this->otherAdvisor->canSeePii());
    }

    public function test_santa_sees_family_name_on_show(): void
    {
        $response = $this->actingAs($this->santa)->get("/family/{$this->family->id}");
        $response->assertOk();
        $response->assertSee('TopSecretFamilyName');
        $response->assertSee('123 Hidden Lane');
    }

    public function test_other_advisor_cannot_view_foreign_family(): void
    {
        // Sanity: an Advisor who does NOT own the family is blocked by
        // existing ownership check (authorizeFamilyAccess).
        $response = $this->actingAs($this->otherAdvisor)->get("/family/{$this->family->id}");
        $response->assertForbidden();
    }

    public function test_owner_advisor_sees_own_family_name_on_show(): void
    {
        $response = $this->actingAs($this->ownerAdvisor)->get("/family/{$this->family->id}");
        $response->assertOk();
        $response->assertSee('TopSecretFamilyName');
    }

    public function test_santa_sees_pii_on_family_index(): void
    {
        $response = $this->actingAs($this->santa)->get('/family');
        $response->assertOk();
        $response->assertSee('TopSecretFamilyName');
    }

    public function test_owner_advisor_can_open_family_edit(): void
    {
        $response = $this->actingAs($this->ownerAdvisor)->get("/family/{$this->family->id}/edit");
        $response->assertOk();
    }

    public function test_delivery_day_template_redacts_pii_for_coordinator(): void
    {
        // Render the Blade view directly to assert on uncompressed HTML
        // (dompdf compresses text streams so substring checks on the binary fail).
        $this->actingAs($this->coordinator);
        $html = view('documents.delivery-day', [
            'families' => Family::whereKey($this->family->id)->get(),
            'showPii' => $this->coordinator->canSeePii(),
        ])->render();

        $this->assertStringNotContainsString('TopSecretFamilyName', $html);
        $this->assertStringNotContainsString('123 Hidden Lane', $html);
        $this->assertStringNotContainsString('555-555-5555', $html);
        $this->assertStringContainsString('#42', $html);
    }

    public function test_delivery_day_template_shows_pii_for_santa(): void
    {
        $this->actingAs($this->santa);
        $html = view('documents.delivery-day', [
            'families' => Family::whereKey($this->family->id)->get(),
            'showPii' => $this->santa->canSeePii(),
        ])->render();

        $this->assertStringContainsString('TopSecretFamilyName', $html);
        $this->assertStringContainsString('123 Hidden Lane', $html);
    }

    public function test_delivery_day_template_shows_pii_for_system_coordinator(): void
    {
        $this->actingAs($this->systemCoordinator);
        $html = view('documents.delivery-day', [
            'families' => Family::whereKey($this->family->id)->get(),
            'showPii' => $this->systemCoordinator->canSeePii(),
        ])->render();

        $this->assertStringContainsString('TopSecretFamilyName', $html);
    }
}
