<?php

namespace App\Console\Commands;

use App\Models\Family;
use App\Models\Setting;
use App\Services\PackingService;
use Illuminate\Console\Command;

class GeneratePackingLists extends Command
{
    protected $signature = 'packing:generate
                            {--season= : Season year (defaults to current)}
                            {--family= : Generate for a specific family ID only}';

    protected $description = 'Generate packing lists for families based on their needs';

    public function handle(PackingService $packingService): int
    {
        $seasonYear = $this->option('season') ?? Setting::get('season_year', date('Y'));
        $familyId = $this->option('family');

        if ($familyId) {
            $family = Family::withoutGlobalScopes()->find($familyId);
            if (!$family) {
                $this->error("Family #{$familyId} not found.");
                return self::FAILURE;
            }

            $list = $packingService->generatePackingList($family, $seasonYear);
            $this->info("Generated packing list for {$family->family_name} (#{$family->family_number}) with {$list->items->count()} items.");
            return self::SUCCESS;
        }

        $this->info("Generating packing lists for season {$seasonYear}...");

        $families = Family::withoutGlobalScopes()
            ->where('season_year', $seasonYear)
            ->get();

        $bar = $this->output->createProgressBar($families->count());
        $bar->start();

        $generated = 0;
        foreach ($families as $family) {
            $packingService->generatePackingList($family, $seasonYear);
            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$generated} packing lists for season {$seasonYear}.");

        return self::SUCCESS;
    }
}
