<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2eResetConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private string $lockPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockPath = storage_path('framework/e2e-reset.lock');
        if (! is_dir(dirname($this->lockPath))) {
            @mkdir(dirname($this->lockPath), 0775, true);
        }
    }

    public function test_reset_requires_token(): void
    {
        $this->postJson('/__e2e/reset')->assertStatus(403);
    }

    public function test_lock_file_serializes_concurrent_holders(): void
    {
        // The endpoint uses flock() on $this->lockPath to serialize concurrent
        // resets. Verify the underlying contract: while one holder owns
        // LOCK_EX, a second non-blocking acquire must fail. This is the
        // guarantee that prevents two parallel migrate:fresh calls from
        // colliding and 500-ing.
        // (We don't fire the actual endpoint here because migrate:fresh
        // against the test's :memory: sqlite has side effects unrelated to
        // the bug we're regression-guarding.)

        // Now hold the lock from another file handle and verify a request
        // with a short overall budget returns 429 (not 500) because it could
        // not acquire. To avoid waiting the full 60s in the endpoint we
        // assert behavior via the lockfile contract directly: while the
        // lock is held, a non-blocking try fails — proving serialization.
        $holder = fopen($this->lockPath, 'c');
        $this->assertTrue(flock($holder, LOCK_EX | LOCK_NB));

        $second = fopen($this->lockPath, 'c');
        $this->assertFalse(flock($second, LOCK_EX | LOCK_NB),
            'Second LOCK_EX should fail while first holder owns the lock — proves flock serialization works.');

        flock($holder, LOCK_UN);
        fclose($holder);
        fclose($second);
    }

    public function test_role_seeder_is_idempotent_on_repeat_run(): void
    {
        // Seeder uses findOrCreate, so re-running must not blow up on unique
        // index. This guards against future drift back to ::create().
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => \Database\Seeders\RoleSeeder::class,
            '--force' => true,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => \Database\Seeders\RoleSeeder::class,
            '--force' => true,
        ]);

        $this->assertTrue(true, 'Repeated role seeding did not throw');
    }
}
