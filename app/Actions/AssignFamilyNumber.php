<?php

namespace App\Actions;

use App\Models\Family;
use App\Models\SchoolRange;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssignFamilyNumber
{
    /**
     * Assign a specific family number to a family.
     */
    public function execute(Family $family, int $number): void
    {
        $family->update(['family_number' => $number]);
    }

    /**
     * Auto-assign numbers to all unassigned families.
     *
     * @return array{assigned: int, errors: string[]}
     */
    public function autoAssignAll(): array
    {
        $unassigned = Family::unassigned()->with('children')->get();
        $schoolRanges = SchoolRange::orderBy('sort_order')->get();
        $assigned = 0;
        $errors = [];

        foreach ($unassigned as $family) {
            $result = $this->assignNext($family, $schoolRanges);

            if ($result === true) {
                $assigned++;
            } else {
                $errors[] = $result;
            }
        }

        return ['assigned' => $assigned, 'errors' => $errors];
    }

    /**
     * Assign the next available number to a single family based on school range.
     *
     * @return true|string True on success, error message on failure
     */
    public function assignNext(Family $family, ?Collection $schoolRanges = null): true|string
    {
        $schoolRanges ??= SchoolRange::orderBy('sort_order')->get();

        $family->loadMissing('children');
        $oldestChild = $family->children->sortByDesc(fn($c) => (int) $c->age)->first();
        $school = $oldestChild?->school;

        if (!$school) {
            return "{$family->family_name}: no children or no school set";
        }

        $range = $schoolRanges->first(function ($r) use ($school) {
            return stripos($school, $r->school_name) !== false
                || stripos($r->school_name, $school) !== false;
        });

        if (!$range) {
            $range = $schoolRanges->firstWhere('school_name', 'Special Case');
        }

        if (!$range) {
            return "{$family->family_name}: no matching school range for '{$school}'";
        }

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use ($family, $range) {
                    $lockedFamily = Family::whereKey($family->id)->lockForUpdate()->firstOrFail();

                    if ($lockedFamily->family_number !== null) {
                        return true;
                    }

                    $nextNumber = $this->nextAvailableNumberForRange($range);
                    if ($nextNumber === null) {
                        return "{$lockedFamily->family_name}: range for '{$range->school_name}' is full";
                    }

                    $lockedFamily->update(['family_number' => $nextNumber]);

                    return true;
                });
            } catch (QueryException $e) {
                if ($attempt === 3 || ! $this->isUniqueFamilyNumberViolation($e)) {
                    return "{$family->family_name}: could not assign a unique family number; please retry";
                }
            }
        }

        return "{$family->family_name}: could not assign a unique family number; please retry";
    }

    private function nextAvailableNumberForRange(SchoolRange $range): ?int
    {
        $usedNumbers = Family::whereBetween('family_number', [$range->range_start, $range->range_end])
            ->lockForUpdate()
            ->pluck('family_number')
            ->all();

        for ($number = $range->range_start; $number <= $range->range_end; $number++) {
            if (! in_array($number, $usedNumbers, true)) {
                return $number;
            }
        }

        return null;
    }

    private function isUniqueFamilyNumberViolation(QueryException $e): bool
    {
        $message = $e->getMessage();

        return $e->getCode() === '23000'
            || str_contains($message, 'families_number_season_unique')
            || str_contains($message, 'families_family_number_unique')
            || str_contains($message, 'UNIQUE constraint failed');
    }
}
