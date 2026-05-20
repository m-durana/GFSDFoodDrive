<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_user_writes_audit_row(): void
    {
        $user = User::create([
            'username' => 'audited_user',
            'first_name' => 'Audit',
            'last_name' => 'User',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $row = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($row);
        $this->assertNull($row->before);
        $this->assertSame('audited_user', $row->after['username'] ?? null);
        // Excluded keys (password is excluded by trait defaults).
        $this->assertArrayNotHasKey('password', $row->after);
    }

    public function test_updating_family_writes_before_after_diff(): void
    {
        $family = Family::create([
            'family_number' => 6661,
            'family_name' => 'Original',
            'address' => '1 Audit St',
            'phone1' => '5555556661',
            'season_year' => date('Y'),
        ]);
        // Drop creation row for clarity
        AuditLog::query()->delete();

        $family->update(['family_name' => 'Updated']);

        $row = AuditLog::where('auditable_type', Family::class)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('Original', $row->before['family_name'] ?? null);
        $this->assertSame('Updated', $row->after['family_name'] ?? null);
        // Only changed key tracked
        $this->assertSame(['family_name'], array_keys($row->after));
    }

    public function test_deleting_child_writes_deleted_audit(): void
    {
        $family = Family::create([
            'family_number' => 6662,
            'family_name' => 'AuditFam',
            'address' => '2 Audit St',
            'phone1' => '5555556662',
            'season_year' => date('Y'),
        ]);
        $child = Child::create([
            'family_id' => $family->id,
            'season_year' => date('Y'),
            'gender' => 'F',
            'age' => 9,
        ]);
        AuditLog::query()->delete();

        $child->delete();

        $row = AuditLog::where('auditable_type', Child::class)
            ->where('auditable_id', $child->id)
            ->where('action', 'deleted')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame(9, (int) ($row->before['age'] ?? 0));
    }

    public function test_audit_excluded_keys_not_logged_on_update(): void
    {
        $user = User::create([
            'username' => 'loc_user',
            'first_name' => 'Loc',
            'last_name' => 'User',
            'password' => 'password123',
            'permission' => 8,
        ]);
        AuditLog::query()->delete();

        $user->update([
            'last_lat' => 47.5,
            'last_lng' => -121.5,
            'last_location_at' => now(),
        ]);

        $this->assertSame(0, AuditLog::where('action', 'updated')->count(),
            'Per-model $auditExclude (last_lat / last_lng / last_location_at) must skip the row entirely.');
    }
}
