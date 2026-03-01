<?php

namespace App\Jobs;

use App\Models\Family;
use App\Models\Season;
use App\Services\AccessImportService;
use App\Services\ExcelImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ImportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        private readonly string $importKey,
        private readonly string $filePath,
        private readonly string $type,
        private readonly int $seasonYear,
        private readonly ?string $accessTable = null,
    ) {}

    public function handle(): void
    {
        $this->progress('running', 'Starting import...');

        $excelService = new ExcelImportService();

        if ($this->accessTable) {
            $accessService = new AccessImportService();
            $rows = $accessService->readTable($this->filePath, $this->accessTable);

            $familyIdMap = null;
            if ($this->type === 'child') {
                $familyIdMap = $this->buildFamilyIdMap($accessService, $this->filePath, $this->seasonYear);
            }

            $this->progress('running', "Importing {$this->type} records from Access table...");

            $result = $this->type === 'family'
                ? $excelService->importFamiliesFromRows($rows, $this->seasonYear)
                : $excelService->importChildrenFromRows($rows, $this->seasonYear, $familyIdMap);
        } else {
            $this->progress('running', "Importing {$this->type} records from Excel...");

            $result = $this->type === 'family'
                ? $excelService->importFamilies($this->filePath, $this->seasonYear)
                : $excelService->importChildren($this->filePath, $this->seasonYear);
        }

        // Update season stats
        $stats = Season::computeStats($this->seasonYear);
        Season::updateOrCreate(['year' => $this->seasonYear], $stats);

        $message = "Imported {$result['imported']} " . ($this->type === 'family' ? 'families' : 'children') . ".";
        if ($result['skipped'] > 0) {
            $message .= " Skipped {$result['skipped']} rows.";
        }
        if (!empty($result['errors'])) {
            $message .= " " . count($result['errors']) . " errors.";
        }

        $this->progress('complete', $message, $result['errors'] ?? []);
    }

    public function failed(\Throwable $exception): void
    {
        $this->progress('failed', 'Import failed: ' . $exception->getMessage());
    }

    private function progress(string $status, string $message, array $errors = []): void
    {
        Cache::put("import:{$this->importKey}", [
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(1));
    }

    private function buildFamilyIdMap(AccessImportService $accessService, string $filePath, int $seasonYear): ?array
    {
        try {
            $tables = $accessService->listTables($filePath);
            $familyTable = collect($tables)->first(fn($t) => stripos($t, 'family') !== false && stripos($t, 'child') === false);
            if (!$familyTable) return null;

            $familyRows = $accessService->readTable($filePath, $familyTable);
            $ourFamilies = Family::withoutGlobalScopes()
                ->where('season_year', $seasonYear)
                ->whereNotNull('family_number')
                ->pluck('id', 'family_number')
                ->toArray();

            $map = [];
            foreach ($familyRows as $fRow) {
                $accessId = $fRow['Family ID'] ?? $fRow['ID'] ?? null;
                $famNum = $fRow['Family Number'] ?? $fRow['FamilyNumber'] ?? null;
                if ($accessId && $famNum && isset($ourFamilies[(int) $famNum])) {
                    $map[$accessId] = $ourFamilies[(int) $famNum];
                }
            }
            return $map;
        } catch (\Exception) {
            return null;
        }
    }
}
