<?php

namespace Tests\Feature\Packing;

use App\Enums\PackingItemStatus;
use App\Http\Middleware\IdempotentRequest;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\User;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * REL-07 / P2 — the 8 scenarios from REL-07-PLAN.md §5.1.
 *
 * Exercises the IdempotentRequest middleware against /api/packing/{list}/item/{item}/pack
 * which is the most-frequent action and the lynchpin of the offline-scanner queue.
 */
class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private PackingService $packingService;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'Santa', 'last_name' => 'Claus',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->packingService = app(PackingService::class);
        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
        GroceryItem::create([
            'name' => 'Tuna', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
    }

    private function makeListWithPendingItem(): array
    {
        $family = Family::create([
            'family_name' => 'Idem Family', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Main St', 'phone1' => '555-1234',
        ]);
        $list = $this->packingService->generatePackingList($family);
        $item = $list->items()->where('status', PackingItemStatus::Pending->value)->first();
        return [$list, $item];
    }

    /** §5.1 #1 — fresh key writes a cache row and packs the item. */
    public function test_first_request_with_key_packs_and_caches(): void
    {
        [$list, $item] = $this->makeListWithPendingItem();
        $key = (string) Str::uuid();

        $resp = $this->actingAs($this->santa)->withHeaders([
            IdempotentRequest::HEADER => $key,
        ])->postJson("/api/packing/{$list->id}/item/{$item->id}/pack");

        $resp->assertOk();
        $resp->assertJson(['success' => true]);
        $this->assertGreaterThan(0, $item->fresh()->quantity_packed);
        $this->assertSame(1, DB::table('idempotency_keys')->where('key', $key)->count());
    }

    /** §5.1 #2 — replay returns the cached body, controller does not run again. */
    public function test_replay_with_same_key_returns_cached_response_no_side_effects(): void
    {
        [$list, $item] = $this->makeListWithPendingItem();
        $key = (string) Str::uuid();
        $url = "/api/packing/{$list->id}/item/{$item->id}/pack";

        $first = $this->actingAs($this->santa)->withHeaders([
            IdempotentRequest::HEADER => $key,
        ])->postJson($url);
        $first->assertOk();
        $packedAfterFirst = $item->fresh()->quantity_packed;

        $second = $this->actingAs($this->santa)->withHeaders([
            IdempotentRequest::HEADER => $key,
        ])->postJson($url);

        $second->assertOk();
        $second->assertHeader('X-Idempotent-Replay', '1');
        $this->assertSame($first->json(), $second->json());
        $this->assertSame($packedAfterFirst, $item->fresh()->quantity_packed, 'Replay must not double-pack.');
    }

    /** §5.1 #3 — replay with different payload still returns the cached first response. */
    public function test_replay_with_different_payload_returns_first_cached_response(): void
    {
        [$list, $item] = $this->makeListWithPendingItem();
        $key = (string) Str::uuid();
        $url = "/api/packing/{$list->id}/item/{$item->id}/pack";

        $first = $this->actingAs($this->santa)->withHeaders([
            IdempotentRequest::HEADER => $key,
        ])->postJson($url, ['volunteer_name' => 'Alice']);

        $second = $this->actingAs($this->santa)->withHeaders([
            IdempotentRequest::HEADER => $key,
        ])->postJson($url, ['volunteer_name' => 'Bob']);

        $second->assertHeader('X-Idempotent-Replay', '1');
        $this->assertSame($first->json(), $second->json());
    }

    /**
     * §5.1 #4 — concurrency: two parallel requests with the same key. We can't
     * truly parallelise inside PHPUnit, but we can prove the cache row exists
     * after the first call and the second short-circuits without re-entering
     * the controller. The lockForUpdate behaviour is implicit in the DB
     * transaction wrapping the middleware.
     */
    public function test_concurrent_same_key_only_runs_controller_once(): void
    {
        [$list, $item] = $this->makeListWithPendingItem();
        $key = (string) Str::uuid();
        $url = "/api/packing/{$list->id}/item/{$item->id}/pack";

        $this->actingAs($this->santa)->withHeaders([IdempotentRequest::HEADER => $key])->postJson($url)->assertOk();
        $packedAfterFirst = $item->fresh()->quantity_packed;

        // Simulate the second "concurrent" attempt; the row already exists,
        // so the middleware short-circuits.
        $this->actingAs($this->santa)->withHeaders([IdempotentRequest::HEADER => $key])->postJson($url)->assertOk();

        $this->assertSame($packedAfterFirst, $item->fresh()->quantity_packed);
        $this->assertSame(1, DB::table('idempotency_keys')->where('key', $key)->count());
    }

    /** §5.1 #5 — same key on a different endpoint does not collide. */
    public function test_same_key_on_different_endpoint_runs_independently(): void
    {
        [$list, $item] = $this->makeListWithPendingItem();
        $key = (string) Str::uuid();

        $this->actingAs($this->santa)->withHeaders([IdempotentRequest::HEADER => $key])
            ->postJson("/api/packing/{$list->id}/item/{$item->id}/pack")->assertOk();

        // /scan is a separate endpoint — should run, not short-circuit.
        $scanResp = $this->actingAs($this->santa)->withHeaders([IdempotentRequest::HEADER => $key])
            ->postJson("/api/packing/{$list->id}/scan", ['code' => 'unknown-barcode-zzz']);

        $scanResp->assertHeaderMissing('X-Idempotent-Replay');
        $this->assertSame(2, DB::table('idempotency_keys')->where('key', $key)->count());
    }

    /** §5.1 #6 — `idempotency:prune` deletes rows older than 24h (default). */
    public function test_prune_removes_keys_older_than_ttl(): void
    {
        DB::table('idempotency_keys')->insert([
            'key' => 'old-key-aaaaaaaaaaaaaaaa',
            'endpoint' => 'api.packing.quickPack',
            'user_id' => $this->santa->id,
            'response_status' => 200,
            'response_body' => '{"success":true}',
            'created_at' => now()->subHours(25),
        ]);
        DB::table('idempotency_keys')->insert([
            'key' => 'fresh-key-bbbbbbbbbbbbbbbb',
            'endpoint' => 'api.packing.quickPack',
            'user_id' => $this->santa->id,
            'response_status' => 200,
            'response_body' => '{"success":true}',
            'created_at' => now()->subHours(1),
        ]);

        $this->artisan('idempotency:prune')->assertExitCode(0);

        $this->assertDatabaseMissing('idempotency_keys', ['key' => 'old-key-aaaaaaaaaaaaaaaa']);
        $this->assertDatabaseHas('idempotency_keys', ['key' => 'fresh-key-bbbbbbbbbbbbbbbb']);
    }

    /** §5.1 #7 — back-compat: no key header → endpoint runs normally, nothing cached. */
    public function test_missing_key_header_passes_through_unchanged(): void
    {
        [$list, $item] = $this->makeListWithPendingItem();

        $resp = $this->actingAs($this->santa)->postJson("/api/packing/{$list->id}/item/{$item->id}/pack");

        $resp->assertOk();
        $resp->assertHeaderMissing('X-Idempotent-Replay');
        $this->assertSame(0, DB::table('idempotency_keys')->count());
    }

    /**
     * §5.1 #8 — a 4xx rejection (item belongs to another list) is cached so the
     * replay returns the same answer. Critical for offline-queue safety: a
     * deterministic 4xx should never be retried forever.
     */
    public function test_conflict_response_is_also_cached_for_replays(): void
    {
        [$listA, $itemA] = $this->makeListWithPendingItem();

        // Build a separate list + item, then ask the API to pack itemB against listA.
        // The controller returns 404 "Item does not belong to this list."
        $familyB = Family::create([
            'family_name' => 'B', 'family_number' => 2,
            'number_of_family_members' => 2, 'number_of_adults' => 1,
            'number_of_children' => 1, 'address' => '2 A', 'phone1' => '555-2222',
        ]);
        $listB = $this->packingService->generatePackingList($familyB);
        $itemB = $listB->items()->first();

        $key = (string) Str::uuid();
        $url = "/api/packing/{$listA->id}/item/{$itemB->id}/pack";

        $first = $this->actingAs($this->santa)->withHeaders([IdempotentRequest::HEADER => $key])->postJson($url);
        $first->assertStatus(404);

        $second = $this->actingAs($this->santa)->withHeaders([IdempotentRequest::HEADER => $key])->postJson($url);
        $second->assertStatus(404);
        $second->assertHeader('X-Idempotent-Replay', '1');
        $this->assertSame($first->json(), $second->json());
    }
}
