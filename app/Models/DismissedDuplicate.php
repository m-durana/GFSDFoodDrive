<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DismissedDuplicate extends Model
{
    protected $fillable = ['family_a_id', 'family_b_id'];

    /**
     * Check if a pair of families has been dismissed as a duplicate.
     */
    public static function isDismissed(int $familyAId, int $familyBId): bool
    {
        $min = min($familyAId, $familyBId);
        $max = max($familyAId, $familyBId);

        return static::where('family_a_id', $min)
            ->where('family_b_id', $max)
            ->exists();
    }

    /**
     * Dismiss a pair of families as not duplicate.
     */
    public static function dismiss(int $familyAId, int $familyBId): void
    {
        $min = min($familyAId, $familyBId);
        $max = max($familyAId, $familyBId);

        static::firstOrCreate([
            'family_a_id' => $min,
            'family_b_id' => $max,
        ]);
    }
}
