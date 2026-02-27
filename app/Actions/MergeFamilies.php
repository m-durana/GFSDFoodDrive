<?php

namespace App\Actions;

use App\Models\Child;
use App\Models\DismissedDuplicate;
use App\Models\Family;

class MergeFamilies
{
    /**
     * Merge one family into another: transfer children, clean up, delete source.
     *
     * @return string The name of the merged (deleted) family
     */
    public function execute(Family $keep, Family $merge): string
    {
        // Move all children from merge to keep
        Child::where('family_id', $merge->id)->update(['family_id' => $keep->id]);

        // Delete dismissed duplicates referencing the merged family
        DismissedDuplicate::where('family_a_id', $merge->id)
            ->orWhere('family_b_id', $merge->id)
            ->delete();

        $mergeName = $merge->family_name;
        $merge->delete();

        return $mergeName;
    }
}
