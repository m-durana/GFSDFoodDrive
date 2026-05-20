<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\DeliveryRoute;
use App\Models\DeliveryTeam;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\SchoolRange;
use App\Models\ShoppingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke-test SoftDeletes on the seven core tables that gained the trait
 * (User, DeliveryRoute, DeliveryTeam, Child, GroceryItem, SchoolRange,
 * ShoppingAssignment).
 *
 * Each model assertion: deleting populates deleted_at, default queries hide
 * the row, withTrashed() finds it, restore() resurrects it.
 */
class SoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_soft_deletes(): void
    {
        $user = User::create([
            'username' => 'soft_user',
            'first_name' => 'Soft',
            'last_name' => 'User',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $id = $user->id;
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $id]);
        $this->assertNull(User::find($id));
        $this->assertNotNull(User::withTrashed()->find($id));

        User::withTrashed()->find($id)->restore();
        $this->assertNotNull(User::find($id));
    }

    public function test_delivery_route_soft_deletes(): void
    {
        $route = DeliveryRoute::create([
            'name' => 'Route SD',
            'season_year' => date('Y'),
        ]);
        $id = $route->id;
        $route->delete();

        $this->assertSoftDeleted('delivery_routes', ['id' => $id]);
        $this->assertNull(DeliveryRoute::find($id));
        $this->assertNotNull(DeliveryRoute::withTrashed()->find($id));
    }

    public function test_delivery_team_soft_deletes(): void
    {
        $team = DeliveryTeam::create([
            'name' => 'Team SD',
            'color' => '#abcdef',
            'season_year' => date('Y'),
        ]);
        $id = $team->id;
        $team->delete();

        $this->assertSoftDeleted('delivery_teams', ['id' => $id]);
        $this->assertNull(DeliveryTeam::find($id));
    }

    public function test_child_soft_deletes(): void
    {
        $family = Family::create([
            'family_number' => 9991,
            'family_name' => 'SD Family',
            'address' => '1 Test St',
            'phone1' => '5555550000',
            'season_year' => date('Y'),
        ]);
        $child = Child::create([
            'family_id' => $family->id,
            'season_year' => date('Y'),
            'gender' => 'F',
            'age' => 7,
        ]);
        $id = $child->id;
        $child->delete();

        $this->assertSoftDeleted('children', ['id' => $id]);
        $this->assertNull(Child::find($id));
    }

    public function test_grocery_item_soft_deletes(): void
    {
        $item = GroceryItem::create([
            'name' => 'SD Bread',
            'category' => 'bakery',
        ]);
        $id = $item->id;
        $item->delete();

        $this->assertSoftDeleted('grocery_items', ['id' => $id]);
        $this->assertNull(GroceryItem::find($id));
    }

    public function test_school_range_soft_deletes(): void
    {
        $range = SchoolRange::create([
            'school_name' => 'SD School',
            'range_start' => 9000,
            'range_end' => 9100,
            'sort_order' => 999,
        ]);
        $id = $range->id;
        $range->delete();

        $this->assertSoftDeleted('school_ranges', ['id' => $id]);
        $this->assertNull(SchoolRange::find($id));
    }

    public function test_shopping_assignment_soft_deletes(): void
    {
        $user = User::create([
            'username' => 'sd_shopper',
            'first_name' => 'SD',
            'last_name' => 'Shopper',
            'password' => 'password123',
            'permission' => 7,
        ]);
        $assignment = ShoppingAssignment::create([
            'user_id' => $user->id,
            'ninja_name' => 'SD Ninja',
            'split_type' => 'deficit',
        ]);
        $id = $assignment->id;
        $assignment->delete();

        $this->assertSoftDeleted('shopping_assignments', ['id' => $id]);
        $this->assertNull(ShoppingAssignment::find($id));
    }
}
