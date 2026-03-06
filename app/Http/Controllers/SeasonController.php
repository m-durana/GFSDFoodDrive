<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportExcelRequest;
use App\Jobs\ImportDataJob;
use App\Models\Child;
use App\Models\Family;
use App\Models\Season;
use App\Models\Setting;
use App\Services\AccessImportService;
use App\Services\ExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::orderByDesc('year')->get();
        $currentYear = (int) Setting::get('season_year', date('Y'));

        // If current season has no Season record yet, compute live stats
        $currentSeason = $seasons->firstWhere('year', $currentYear);
        if (! $currentSeason) {
            $currentStats = Season::computeStats($currentYear);
            $currentStats['year'] = $currentYear;
            $currentStats['archived_at'] = null;
        } else {
            $currentStats = null;
        }

        // Chart data
        $chartYears = $seasons->pluck('year')->toArray();
        $chartFamilies = $seasons->pluck('total_families')->toArray();
        $chartChildren = $seasons->pluck('total_children')->toArray();

        if ($currentStats) {
            array_unshift($chartYears, $currentYear);
            array_unshift($chartFamilies, $currentStats['total_families']);
            array_unshift($chartChildren, $currentStats['total_children']);
        }

        return view('santa.seasons.index', compact(
            'seasons', 'currentYear', 'currentStats',
            'chartYears', 'chartFamilies', 'chartChildren'
        ));
    }

    public function show(Season $season)
    {
        return view('santa.seasons.show', compact('season'));
    }

    public function archive(Request $request)
    {
        $currentYear = (int) Setting::get('season_year', date('Y'));

        $stats = Season::computeDetailedStats($currentYear);
        Season::updateOrCreate(
            ['year' => $currentYear],
            array_merge($stats, [
                'archived_at' => now(),
                'notes' => $request->input('notes'),
            ])
        );

        Setting::set('season_year', $currentYear + 1);

        return redirect()->route('santa.seasons.index')
            ->with('success', "Season {$currentYear} archived. Now in season " . ($currentYear + 1) . ".");
    }

    public function families(Season $season)
    {
        $families = Family::withoutGlobalScopes()
            ->where('season_year', $season->year)
            ->withCount('children')
            ->orderBy('family_number')
            ->paginate(50);

        return view('santa.seasons.families', compact('season', 'families'));
    }

    public function importForm()
    {
        $currentYear = (int) Setting::get('season_year', date('Y'));
        $existingSeasons = Season::orderByDesc('year')->pluck('year')->toArray();
        $accessDriver = AccessImportService::availableDriver();

        // Scan for pre-loaded legacy databases
        $legacyFiles = $this->scanLegacyDatabases();

        return view('santa.seasons.import', compact('currentYear', 'existingSeasons', 'accessDriver', 'legacyFiles'));
    }

    private function scanLegacyDatabases(): array
    {
        $legacyDir = base_path('.claude/Legacy DBS');
        $files = [];

        if (! is_dir($legacyDir)) {
            return $files;
        }

        foreach (scandir($legacyDir) as $yearDir) {
            if ($yearDir === '.' || $yearDir === '..' || ! is_numeric($yearDir)) {
                continue;
            }

            $yearPath = "{$legacyDir}/{$yearDir}";
            if (! is_dir($yearPath)) {
                continue;
            }

            $yearFiles = [];
            foreach (glob("{$yearPath}/*.{accdb,mdb,xlsx,xls}", GLOB_BRACE) as $file) {
                $basename = basename($file);
                // Skip backup/survey-only files, prioritize main database
                $isMainDb = str_contains(strtolower($basename), 'database_be') || str_contains(strtolower($basename), 'fooddrivedatabase');
                $yearFiles[] = [
                    'path' => $file,
                    'name' => $basename,
                    'ext' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    'size' => filesize($file),
                    'is_main' => $isMainDb,
                ];
            }

            // Sort: main databases first
            usort($yearFiles, fn($a, $b) => $b['is_main'] <=> $a['is_main']);

            if (! empty($yearFiles)) {
                $files[(int) $yearDir] = $yearFiles;
            }
        }

        ksort($files);
        return $files;
    }

    public function previewImport(ImportExcelRequest $request)
    {
        $file = $request->file('file');
        $seasonYear = (int) $request->input('season_year');
        $ext = strtolower($file->getClientOriginalExtension());

        $path = $file->store('imports', 'local');
        $fullPath = storage_path('app/' . $path);

        // Access database → show table picker
        if (in_array($ext, ['accdb', 'mdb'])) {
            return $this->previewAccessFile($fullPath, $path, $seasonYear);
        }

        // Excel file → direct preview
        $type = $request->input('type');
        $service = new ExcelImportService();
        $preview = $service->preview($fullPath, $type);

        return view('santa.seasons.import-preview', compact('preview', 'type', 'seasonYear', 'path'));
    }

    /**
     * Show table picker for Access database files.
     */
    private function previewAccessFile(string $fullPath, string $path, int $seasonYear)
    {
        $accessService = new AccessImportService();

        try {
            $tables = $accessService->listTables($fullPath);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('santa.seasons.access-tables', compact('tables', 'path', 'seasonYear'));
    }

    /**
     * Preview a specific table from an Access database.
     */
    public function previewAccessTable(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'table' => ['required', 'string'],
            'type' => ['required', 'in:family,child'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
        ]);

        $fullPath = storage_path('app/' . $request->input('path'));
        if (! file_exists($fullPath)) {
            return redirect()->route('santa.seasons.import')->with('error', 'Upload expired. Please upload again.');
        }

        $table = $request->input('table');
        $type = $request->input('type');
        $seasonYear = (int) $request->input('season_year');
        $path = $request->input('path');

        $accessService = new AccessImportService();
        $rows = $accessService->readTable($fullPath, $table);

        $excelService = new ExcelImportService();
        $preview = $excelService->previewFromRows($rows, $type);

        $isAccess = true;
        $accessTable = $table;

        return view('santa.seasons.import-preview', compact(
            'preview', 'type', 'seasonYear', 'path',
            'isAccess', 'accessTable'
        ));
    }

    public function importStatus(string $key): JsonResponse
    {
        $data = Cache::get("import:{$key}");
        if (!$data) {
            return response()->json(['status' => 'unknown', 'message' => 'No import found with this key.']);
        }
        return response()->json($data);
    }

    public function executeImport(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'type' => ['required', 'in:family,child'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
            'access_table' => ['nullable', 'string'],
            'background' => ['nullable', 'boolean'],
        ]);

        $fullPath = storage_path('app/' . $request->input('path'));
        if (! file_exists($fullPath)) {
            return back()->with('error', 'Upload expired. Please upload the file again.');
        }

        $type = $request->input('type');
        $seasonYear = (int) $request->input('season_year');
        $accessTable = $request->input('access_table');

        // Run imports in background queue by default (use ?sync=true for synchronous)
        if (!$request->boolean('sync')) {
            $importKey = Str::random(16);
            Cache::put("import:{$importKey}", [
                'status' => 'queued',
                'message' => 'Import queued...',
                'errors' => [],
                'updated_at' => now()->toIso8601String(),
            ], now()->addHours(1));

            ImportDataJob::dispatch($importKey, $fullPath, $type, $seasonYear, $accessTable);

            return redirect()->route('santa.seasons.import')
                ->with('import_key', $importKey)
                ->with('success', 'Import started in background. Progress will update automatically.');
        }

        $excelService = new ExcelImportService();

        if ($accessTable) {
            // Import from Access database table
            $accessService = new AccessImportService();
            $rows = $accessService->readTable($fullPath, $accessTable);

            // For Access child tables, build a map from Access Family ID → our family ID
            $familyIdMap = null;
            if ($type === 'child') {
                // Access "Family ID" is an internal Access auto-increment.
                // We need to map it: find families by season_year, build
                // Access Family ID → our family record using the family_number.
                // But the child table might reference "Family ID" not "Family Number".
                // We'll try to build the map by reading the Family Table from the same DB.
                try {
                    $familyRows = $accessService->readTable($fullPath, 'Family Table');
                    $ourFamilies = Family::withoutGlobalScopes()
                        ->where('season_year', $seasonYear)
                        ->whereNotNull('family_number')
                        ->pluck('id', 'family_number')
                        ->toArray();

                    $familyIdMap = [];
                    foreach ($familyRows as $fRow) {
                        $accessId = $fRow['Family ID'] ?? null;
                        $famNum = $fRow['Family Number'] ?? null;
                        if ($accessId && $famNum && isset($ourFamilies[(int) $famNum])) {
                            $familyIdMap[$accessId] = $ourFamilies[(int) $famNum];
                        }
                    }
                } catch (\Exception $e) {
                    // Fall through — children will link by family_number if available
                }
            }

            $result = $type === 'family'
                ? $excelService->importFamiliesFromRows($rows, $seasonYear)
                : $excelService->importChildrenFromRows($rows, $seasonYear, $familyIdMap);
        } else {
            // Import from Excel file
            $result = $type === 'family'
                ? $excelService->importFamilies($fullPath, $seasonYear)
                : $excelService->importChildren($fullPath, $seasonYear);
        }

        // Don't delete Access files yet if user might import more tables
        if (! $accessTable) {
            @unlink($fullPath);
        }

        // Auto-create or update Season record
        $stats = Season::computeStats($seasonYear);
        Season::updateOrCreate(['year' => $seasonYear], $stats);

        $message = "Imported {$result['imported']} " . ($type === 'family' ? 'families' : 'children') . ".";
        if ($result['skipped'] > 0) {
            $message .= " Skipped {$result['skipped']} rows.";
        }
        if (! empty($result['errors'])) {
            $message .= " " . count($result['errors']) . " errors.";
        }

        // If Access file, redirect back to table picker so user can import more tables
        if ($accessTable) {
            return redirect()->route('santa.seasons.accessTables', [
                'path' => $request->input('path'),
                'season_year' => $seasonYear,
            ])->with('success', $message)->with('import_errors', $result['errors']);
        }

        return redirect()->route('santa.seasons.import')
            ->with('success', $message)
            ->with('import_errors', $result['errors']);
    }

    /**
     * Show Access table picker (GET — for redirect after import).
     */
    public function accessTables(Request $request)
    {
        $path = $request->input('path');
        $seasonYear = (int) $request->input('season_year');
        $fullPath = storage_path('app/' . $path);

        if (! file_exists($fullPath)) {
            return redirect()->route('santa.seasons.import')->with('error', 'Upload expired.');
        }

        $accessService = new AccessImportService();
        $tables = $accessService->listTables($fullPath);

        return view('santa.seasons.access-tables', compact('tables', 'path', 'seasonYear'));
    }

    /**
     * Import all tables (Family Table + Child Table) from an Access database in one click.
     */
    public function importAllAccess(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'season_year' => ['required', 'integer', 'between:2000,2099'],
        ]);

        $fullPath = storage_path('app/' . $request->input('path'));
        if (! file_exists($fullPath)) {
            return back()->with('error', 'Upload expired. Please upload again.');
        }

        $seasonYear = (int) $request->input('season_year');
        $accessService = new AccessImportService();
        $excelService = new ExcelImportService();
        $tables = $accessService->listTables($fullPath);

        $messages = [];
        $allErrors = [];

        // 1. Import Family Table first
        $familyTable = collect($tables)->first(fn($t) => stripos($t, 'family') !== false && stripos($t, 'child') === false);
        if ($familyTable) {
            $rows = $accessService->readTable($fullPath, $familyTable);
            $result = $excelService->importFamiliesFromRows($rows, $seasonYear);
            $messages[] = "Families ({$familyTable}): {$result['imported']} imported, {$result['skipped']} skipped.";
            $allErrors = array_merge($allErrors, $result['errors']);
        }

        // 2. Import Child Table, building Access Family ID → our family ID map
        $childTable = collect($tables)->first(fn($t) => stripos($t, 'child') !== false);
        if ($childTable) {
            $childRows = $accessService->readTable($fullPath, $childTable);

            // Build family ID map from Access's Family Table
            $familyIdMap = null;
            if ($familyTable) {
                try {
                    $familyRows = $accessService->readTable($fullPath, $familyTable);
                    $ourFamilies = Family::withoutGlobalScopes()
                        ->where('season_year', $seasonYear)
                        ->whereNotNull('family_number')
                        ->pluck('id', 'family_number')
                        ->toArray();

                    $familyIdMap = [];
                    foreach ($familyRows as $fRow) {
                        $accessId = $fRow['Family ID'] ?? $fRow['ID'] ?? null;
                        $famNum = $fRow['Family Number'] ?? $fRow['FamilyNumber'] ?? null;
                        if ($accessId && $famNum && isset($ourFamilies[(int) $famNum])) {
                            $familyIdMap[$accessId] = $ourFamilies[(int) $famNum];
                        }
                    }
                } catch (\Exception $e) {
                    // Fall through
                }
            }

            $result = $excelService->importChildrenFromRows($childRows, $seasonYear, $familyIdMap);
            $messages[] = "Children ({$childTable}): {$result['imported']} imported, {$result['skipped']} skipped.";
            $allErrors = array_merge($allErrors, $result['errors']);
        }

        if (empty($messages)) {
            return back()->with('error', 'No Family Table or Child Table found in this database.');
        }

        // Auto-create Season record
        $stats = Season::computeStats($seasonYear);
        Season::updateOrCreate(['year' => $seasonYear], $stats);

        @unlink($fullPath);

        return redirect()->route('santa.seasons.import')
            ->with('success', implode(' ', $messages))
            ->with('import_errors', $allErrors);
    }

    /**
     * Import a pre-loaded legacy database file (from .claude/Legacy DBS/).
     */
    public function importLegacy(Request $request)
    {
        $request->validate([
            'legacy_path' => 'required|string',
            'season_year' => 'required|integer|min:2000|max:2099',
        ]);

        $filePath = $request->input('legacy_path');
        $seasonYear = (int) $request->input('season_year');

        // Security: only allow files under the legacy directory
        $legacyDir = realpath(base_path('.claude/Legacy DBS'));
        $realPath = realpath($filePath);

        if (! $realPath || ! $legacyDir || ! str_starts_with($realPath, $legacyDir)) {
            return redirect()->route('santa.seasons.import')
                ->with('error', 'Invalid file path.');
        }

        if (! file_exists($realPath)) {
            return redirect()->route('santa.seasons.import')
                ->with('error', 'File not found.');
        }

        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

        // Access database → copy to storage and redirect to table picker
        if (in_array($ext, ['accdb', 'mdb'])) {
            $storagePath = 'imports/legacy_' . basename($realPath);
            $destPath = storage_path('app/' . $storagePath);

            if (! is_dir(dirname($destPath))) {
                mkdir(dirname($destPath), 0755, true);
            }
            copy($realPath, $destPath);

            return redirect()->route('santa.seasons.accessTables', [
                'path' => $storagePath,
                'season_year' => $seasonYear,
            ]);
        }

        // Excel file → direct preview
        $storagePath = 'imports/legacy_' . basename($realPath);
        $destPath = storage_path('app/' . $storagePath);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }
        copy($realPath, $destPath);

        $type = 'family'; // Default to family for Excel
        $service = new ExcelImportService();
        $preview = $service->preview($destPath, $type);
        $path = $storagePath;

        return view('santa.seasons.import-preview', compact('preview', 'type', 'seasonYear', 'path'));
    }

    /**
     * Bulk-import all legacy database files that haven't been imported yet.
     */
    public function importAllLegacy()
    {
        set_time_limit(0);

        $legacyFiles = $this->scanLegacyDatabases();
        $existingSeasons = Season::pluck('year')->toArray();
        $accessService = new AccessImportService();
        $excelService = new ExcelImportService();

        $totalFamilies = 0;
        $totalChildren = 0;
        $yearsImported = 0;
        $allErrors = [];

        foreach ($legacyFiles as $year => $files) {
            if (in_array($year, $existingSeasons)) {
                continue;
            }

            // Find the main _be database file
            $mainFile = collect($files)->firstWhere('is_main', true);
            if (!$mainFile) {
                continue;
            }

            $ext = $mainFile['ext'];
            $filePath = $mainFile['path'];

            if (!in_array($ext, ['accdb', 'mdb'])) {
                continue;
            }

            try {
                $tables = $accessService->listTables($filePath);

                // Import families
                $familyTable = collect($tables)->first(fn($t) => stripos($t, 'family') !== false && stripos($t, 'child') === false);
                if ($familyTable) {
                    $rows = $accessService->readTable($filePath, $familyTable);
                    $result = $excelService->importFamiliesFromRows($rows, $year);
                    $totalFamilies += $result['imported'];
                    $allErrors = array_merge($allErrors, array_map(fn($e) => "[{$year}] {$e}", $result['errors']));
                }

                // Import children
                $childTable = collect($tables)->first(fn($t) => stripos($t, 'child') !== false);
                if ($childTable) {
                    $childRows = $accessService->readTable($filePath, $childTable);

                    $familyIdMap = null;
                    if ($familyTable) {
                        try {
                            $familyRows = $accessService->readTable($filePath, $familyTable);
                            $ourFamilies = Family::withoutGlobalScopes()
                                ->where('season_year', $year)
                                ->whereNotNull('family_number')
                                ->pluck('id', 'family_number')
                                ->toArray();

                            $familyIdMap = [];
                            foreach ($familyRows as $fRow) {
                                $accessId = $fRow['Family ID'] ?? $fRow['ID'] ?? null;
                                $famNum = $fRow['Family Number'] ?? $fRow['FamilyNumber'] ?? null;
                                if ($accessId && $famNum && isset($ourFamilies[(int) $famNum])) {
                                    $familyIdMap[$accessId] = $ourFamilies[(int) $famNum];
                                }
                            }
                        } catch (\Exception $e) {
                            // Fall through
                        }
                    }

                    $result = $excelService->importChildrenFromRows($childRows, $year, $familyIdMap);
                    $totalChildren += $result['imported'];
                    $allErrors = array_merge($allErrors, array_map(fn($e) => "[{$year}] {$e}", $result['errors']));
                }

                // Create season record
                $stats = Season::computeStats($year);
                Season::updateOrCreate(['year' => $year], $stats);
                $yearsImported++;
            } catch (\Exception $e) {
                $allErrors[] = "[{$year}] Failed: " . $e->getMessage();
            }
        }

        if ($yearsImported === 0) {
            return redirect()->route('santa.seasons.import')
                ->with('error', 'No new years to import (all already exist or no main _be files found).');
        }

        return redirect()->route('santa.seasons.import')
            ->with('success', "Imported {$yearsImported} years: {$totalFamilies} families, {$totalChildren} children.")
            ->with('import_errors', array_slice($allErrors, 0, 50));
    }
}
