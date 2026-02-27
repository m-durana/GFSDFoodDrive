<?php

namespace App\Actions;

use App\Models\Family;
use App\Models\SchoolRange;
use Illuminate\Support\Collection;

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

        $nextNumber = $range->nextAvailableNumber();
        if ($nextNumber === null) {
            return "{$family->family_name}: range for '{$range->school_name}' is full";
        }

        $family->update(['family_number' => $nextNumber]);
        return true;
    }
}
