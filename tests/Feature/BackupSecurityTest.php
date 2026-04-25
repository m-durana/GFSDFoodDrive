<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'santa_security',
            'first_name' => 'Security',
            'last_name' => 'Santa',
            'password' => 'password',
            'permission' => 9,
        ]);
    }

    protected function tearDown(): void
    {
        $this->removeTestFile(storage_path('backup_escape.sqlite'));
        $this->removeTestFile(storage_path('backups/backup_valid_security.sqlite'));
        $this->removeTestFile(storage_path('backups/backup_'));
        @rmdir(storage_path('backups/backup_'));

        parent::tearDown();
    }

    public function test_rollback_rejects_path_traversal_filename(): void
    {
        $backupDir = storage_path('backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        if (! is_dir($backupDir . '/backup_')) {
            mkdir($backupDir . '/backup_', 0755, true);
        }

        file_put_contents(storage_path('backup_escape.sqlite'), 'not a backup');

        Artisan::shouldReceive('call')->never();

        $response = $this->actingAs($this->santa)->post(route('santa.rollbackBackup'), [
            'filename' => 'backup_/../../backup_escape.sqlite',
        ]);

        $response->assertRedirect(route('santa.backups'));
        $response->assertSessionHas('error', 'Backup not found.');
    }

    public function test_download_rejects_non_backup_basename(): void
    {
        $backupDir = storage_path('backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        file_put_contents($backupDir . '/not_backup.sqlite', 'not a backup');

        $this->actingAs($this->santa)
            ->get('/santa/backups/download/not_backup.sqlite')
            ->assertNotFound();
    }

    public function test_download_allows_real_backup_file_inside_backup_directory(): void
    {
        $backupDir = storage_path('backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        file_put_contents($backupDir . '/backup_valid_security.sqlite', 'backup content');

        $this->actingAs($this->santa)
            ->get('/santa/backups/download/backup_valid_security.sqlite')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    private function removeTestFile(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
