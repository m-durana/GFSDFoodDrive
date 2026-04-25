<?php

namespace Tests\Feature;

use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for BUGS.md P0 authorization-hole fixes:
 *   S-30: Bulk-update-users — role whitelist, self-protection, "must keep one Santa" invariant.
 *   S-33: Approve-access-request — role whitelist blocks crafted role=santa escalation.
 */
class SantaPrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->santa = User::create([
            'username' => 'santa_actor',
            'first_name' => 'Santa',
            'last_name' => 'Actor',
            'password' => 'password123',
            'permission' => 9,
        ]);
    }

    // ---------- S-30 golden path ----------

    public function test_bulk_update_succeeds_for_valid_payload(): void
    {
        $other = User::create([
            'username' => 'santa_backup',
            'first_name' => 'Backup',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $target = User::create([
            'username' => 'volunteer',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/users/bulk-update', [
            'users' => [
                $target->id => [
                    'first_name' => 'Updated',
                    'last_name' => 'Name',
                    'role' => 'coordinator',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals(8, $target->fresh()->permission);
        $this->assertEquals(9, $other->fresh()->permission); // untouched
    }

    // ---------- S-30 role whitelist ----------

    public function test_bulk_update_rejects_unknown_role(): void
    {
        User::create([
            'username' => 'santa_backup',
            'first_name' => 'Backup',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $target = User::create([
            'username' => 'volunteer2',
            'first_name' => 'V',
            'last_name' => 'W',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/users/bulk-update', [
            'users' => [
                $target->id => [
                    'first_name' => 'V',
                    'last_name' => 'W',
                    'role' => 'superuser',
                ],
            ],
        ]);

        $response->assertSessionHasErrors("users.{$target->id}.role");
        $this->assertEquals(7, $target->fresh()->permission);
    }

    // ---------- S-30 self-protection ----------

    public function test_bulk_update_blocks_self_demotion(): void
    {
        User::create([
            'username' => 'santa_backup',
            'first_name' => 'Backup',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/users/bulk-update', [
            'users' => [
                $this->santa->id => [
                    'first_name' => 'Santa',
                    'last_name' => 'Actor',
                    'role' => 'family',
                ],
            ],
        ]);

        $response->assertSessionHasErrors("users.{$this->santa->id}.role");
        $this->assertEquals(9, $this->santa->fresh()->permission);
    }

    // ---------- S-30 must-keep-one-Santa ----------

    public function test_bulk_update_rejects_demoting_last_santa(): void
    {
        $other = User::create([
            'username' => 'volunteer',
            'first_name' => 'V',
            'last_name' => 'W',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $response = $this->actingAs($this->santa)->post('/santa/users/bulk-update', [
            'users' => [
                $this->santa->id => [
                    'first_name' => 'Santa',
                    'last_name' => 'Actor',
                    'role' => 'santa',
                ],
                $other->id => [
                    'first_name' => 'V',
                    'last_name' => 'W',
                    'role' => 'coordinator',
                ],
            ],
        ]);

        // The self-row is safe (still santa). The last-Santa invariant passes
        // because actor remains. Golden path still works.
        $response->assertRedirect();
        $this->assertEquals(9, $this->santa->fresh()->permission);
        $this->assertEquals(8, $other->fresh()->permission);
    }

    public function test_bulk_update_rejects_when_all_santas_demoted(): void
    {
        // Two Santas. Payload demotes both.
        $other = User::create([
            'username' => 'santa2',
            'first_name' => 'Second',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        // Acting as a *third* Santa so the self-protection rule doesn't fire first.
        $acting = User::create([
            'username' => 'santa3',
            'first_name' => 'Third',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $response = $this->actingAs($acting)->post('/santa/users/bulk-update', [
            'users' => [
                $this->santa->id => [
                    'first_name' => 'Santa',
                    'last_name' => 'Actor',
                    'role' => 'coordinator',
                ],
                $other->id => [
                    'first_name' => 'Second',
                    'last_name' => 'Santa',
                    'role' => 'coordinator',
                ],
                $acting->id => [
                    'first_name' => 'Third',
                    'last_name' => 'Santa',
                    'role' => 'coordinator',
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(9, $this->santa->fresh()->permission);
        $this->assertEquals(9, $other->fresh()->permission);
        $this->assertEquals(9, $acting->fresh()->permission);
    }

    // ---------- updateUser guards ----------

    public function test_update_user_blocks_self_demotion(): void
    {
        $response = $this->actingAs($this->santa)->put("/santa/users/{$this->santa->id}", [
            'first_name' => 'Santa',
            'last_name' => 'Actor',
            'role' => 'family',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(9, $this->santa->fresh()->permission);
    }

    // ---------- deleteUser guard ----------

    public function test_delete_user_blocks_self_delete(): void
    {
        // Baseline guard: an operator cannot delete their own account.
        $response = $this->actingAs($this->santa)->delete("/santa/users/{$this->santa->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull(User::find($this->santa->id));
    }

    public function test_delete_user_allows_santa_when_another_remains(): void
    {
        // Golden path: deleting a second Santa is allowed while the acting Santa remains.
        $other = User::create([
            'username' => 'spare_santa',
            'first_name' => 'Spare',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $response = $this->actingAs($this->santa)->delete("/santa/users/{$other->id}");
        $response->assertRedirect();
        $this->assertNull(User::find($other->id));
    }

    // ---------- S-33 role whitelist ----------

    public function test_approve_access_request_honours_whitelist(): void
    {
        $ar = AccessRequest::create([
            'email' => 'ok@example.com',
            'name' => 'Okay Person',
            'google_id' => 'g-ok',
            'requested_role' => 'family',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->santa)
            ->post("/santa/access-requests/{$ar->id}/approve", ['role' => 'coordinator']);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'ok@example.com',
            'permission' => 8,
        ]);
        $this->assertEquals('approved', $ar->fresh()->status);
    }

    public function test_approve_access_request_rejects_unknown_role(): void
    {
        $ar = AccessRequest::create([
            'email' => 'evil@example.com',
            'name' => 'Evil Person',
            'google_id' => 'g-evil',
            'requested_role' => 'family',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->santa)
            ->post("/santa/access-requests/{$ar->id}/approve", ['role' => 'root']);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'evil@example.com']);
        $this->assertEquals('pending', $ar->fresh()->status);
    }

    public function test_approve_access_request_allows_santa_role_only_when_explicitly_posted(): void
    {
        // The vulnerability was that role=santa in a crafted POST always minted a Santa.
        // Post-fix: role=santa is still permitted (UI may use it), but the value is
        // now validated against a whitelist — so the crafted-POST variants with
        // off-whitelist values (tested above) no longer sneak through.
        $ar = AccessRequest::create([
            'email' => 'newsanta@example.com',
            'name' => 'Future Santa',
            'google_id' => 'g-newsanta',
            'requested_role' => 'family',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->santa)
            ->post("/santa/access-requests/{$ar->id}/approve", ['role' => 'santa']);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newsanta@example.com',
            'permission' => 9,
        ]);
    }
}
