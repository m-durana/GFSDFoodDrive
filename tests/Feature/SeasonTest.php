<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\Season;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $familyUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->familyUser = User::create([
            'username' => 'family', 'first_name' => 'F', 'last_name' => 'U',
            'password' => 'password', 'permission' => 7,
        ]);
    }

    public function test_seasons_index_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.seasons.index'));
        $response->assertOk();
    }

    public function test_season_show_page_loads(): void
    {
        $season = Season::create(['year' => 2025]);

        $response = $this->actingAs($this->santa)->get(route('santa.seasons.show', $season));
        $response->assertOk();
    }

    public function test_season_archive_creates_snapshot(): void
    {
        $currentYear = Setting::get('season_year', date('Y'));

        $family = Family::create([
            'family_name' => 'Archive Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
            'delivery_status' => DeliveryStatus::Delivered,
            'season_year' => $currentYear,
        ]);
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => 6,
            'gift_level' => GiftLevel::Full,
            'season_year' => $currentYear,
        ]);

        $response = $this->actingAs($this->santa)->post(route('santa.seasons.archive'));
        $response->assertRedirect();

        // After archive, season_year is incremented, so look for the original year
        $season = Season::where('year', $currentYear)->first();
        $this->assertNotNull($season);
        $this->assertEquals(1, $season->total_families);
        $this->assertEquals(1, $season->total_children);
    }

    public function test_import_form_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.seasons.import'));
        $response->assertOk();
    }

    public function test_family_user_cannot_access_seasons(): void
    {
        $response = $this->actingAs($this->familyUser)->get(route('santa.seasons.index'));
        $response->assertForbidden();
    }

    // ── Season::computeStats ──────────────────────────────────────

    public function test_compute_stats_returns_correct_counts(): void
    {
        $year = Setting::get('season_year', date('Y'));

        $family = Family::create([
            'family_name' => 'Stats Test', 'family_number' => 1,
            'number_of_family_members' => 5, 'number_of_adults' => 2,
            'number_of_children' => 3, 'phone1' => '555-1234', 'address' => '1 Main St',
            'delivery_status' => DeliveryStatus::Delivered,
            'preferred_language' => 'Spanish',
            'season_year' => $year,
        ]);

        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female', 'age' => 8,
            'gift_level' => GiftLevel::Full,
            'season_year' => $year,
        ]);
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => 5,
            'gift_level' => GiftLevel::None,
            'adoption_token' => 'adopted-test',
            'season_year' => $year,
        ]);

        $stats = Season::computeStats((int) $year);

        $this->assertEquals(1, $stats['total_families']);
        $this->assertEquals(2, $stats['total_children']);
        $this->assertEquals(5, $stats['total_family_members']);
        $this->assertEquals(1, $stats['gifts_level_3']);
        $this->assertEquals(1, $stats['deliveries_completed']);
        $this->assertEquals(1, $stats['tags_adopted']);
        $this->assertEquals(50.0, $stats['adoption_rate']);
        $this->assertArrayHasKey('Spanish', $stats['families_by_language']);
    }

    public function test_compute_stats_handles_empty_season(): void
    {
        $stats = Season::computeStats(1999);

        $this->assertEquals(0, $stats['total_families']);
        $this->assertEquals(0, $stats['total_children']);
        $this->assertEquals(0, $stats['adoption_rate']);
    }
}
