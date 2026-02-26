<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--retain-days=7 : Number of days to keep backups} {--force : Create backup even if unchanged}';
    protected $description = 'Create a timestamped database backup and clean up old backups';

    public function handle(): int
    {
        $driver = config('database.default');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = storage_path('backups');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $success = match ($driver) {
            'sqlite' => $this->backupSqlite($backupDir, $timestamp),
            'mysql' => $this->backupMysql($backupDir, $timestamp),
            default => $this->logError("Unsupported database driver: {$driver}"),
        };

        if (! $success) {
            return self::FAILURE;
        }

        $this->cleanup($backupDir, (int) $this->option('retain-days'));

        return self::SUCCESS;
    }

    private function backupSqlite(string $backupDir, string $timestamp): bool
    {
        $dbPath = config('database.connections.sqlite.database');

        if (! file_exists($dbPath)) {
            $this->error("SQLite database not found: {$dbPath}");
            return false;
        }

        // Check if anything changed since last backup (skip if identical)
        if (! $this->option('force')) {
            $latestBackup = $this->getLatestBackup($backupDir);
            if ($latestBackup && md5_file($dbPath) === md5_file($latestBackup)) {
                // Update the timestamp of the latest backup to show it was checked
                touch($latestBackup);
                $this->info("No changes since last backup. Updated timestamp on " . basename($latestBackup));
                return true;
            }
        }

        $backupFile = "{$backupDir}/backup_{$timestamp}.sqlite";
        copy($dbPath, $backupFile);

        $sizeMb = round(filesize($backupFile) / 1024 / 1024, 2);
        $this->info("SQLite backup created: {$backupFile} ({$sizeMb} MB)");
        return true;
    }

    private function backupMysql(string $backupDir, string $timestamp): bool
    {
        $config = config('database.connections.mysql');
        $backupFile = "{$backupDir}/backup_{$timestamp}.sql";

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error("mysqldump failed with code {$returnCode}");
            return false;
        }

        $sizeMb = round(filesize($backupFile) / 1024 / 1024, 2);
        $this->info("MySQL backup created: {$backupFile} ({$sizeMb} MB)");
        return true;
    }

    private function cleanup(string $backupDir, int $retainDays): void
    {
        $cutoff = now()->subDays($retainDays)->timestamp;
        $deleted = 0;

        foreach (glob("{$backupDir}/backup_*") as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s) (>{$retainDays} days).");
        }
    }

    private function getLatestBackup(string $backupDir): ?string
    {
        $files = glob("{$backupDir}/backup_*");
        if (empty($files)) {
            return null;
        }

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        return $files[0];
    }

    private function logError(string $message): bool
    {
        $this->error($message);
        return false;
    }
}
