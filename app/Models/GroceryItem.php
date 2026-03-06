<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroceryItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'qty_1', 'qty_2', 'qty_3', 'qty_4',
        'qty_5', 'qty_6', 'qty_7', 'qty_8',
        'conditional',
        'condition_field',
        'sort_order',
        'dietary_flags',
        'dietary_tags',
    ];

    protected function casts(): array
    {
        return [
            'conditional' => 'boolean',
            'dietary_flags' => 'array',
            'dietary_tags' => 'array',
        ];
    }

    /**
     * Check if this grocery item is compatible with a family's dietary restrictions.
     * Returns false if any family restriction overlaps with this item's dietary flags.
     *
     * Restriction mapping:
     * - nut_free → item has 'nuts' flag
     * - halal → item has 'pork' or 'alcohol' flag
     * - kosher → item has 'pork' or 'shellfish' flag
     * - vegetarian → item has 'meat' flag
     * - gluten_free → item has 'gluten' flag
     * - dairy_free → item has 'dairy' flag
     */
    public function isCompatibleWith(array $familyRestrictions): bool
    {
        if (empty($familyRestrictions) || empty($this->dietary_flags)) {
            return true;
        }

        $conflictMap = [
            'nut_free' => ['nuts'],
            'halal' => ['pork', 'alcohol'],
            'kosher' => ['pork', 'shellfish'],
            'vegetarian' => ['meat'],
            'gluten_free' => ['gluten'],
            'dairy_free' => ['dairy'],
        ];

        $itemFlags = $this->dietary_flags ?? [];

        foreach ($familyRestrictions as $restriction) {
            $conflictingFlags = $conflictMap[$restriction] ?? [];
            if (array_intersect($conflictingFlags, $itemFlags)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the quantity for a given family size (1-8+).
     * Sizes above 8 use the qty_8 column (largest bracket).
     */
    public function quantityForSize(int $familyMembers): int
    {
        $size = min(max($familyMembers, 1), 8);

        return (int) $this->{"qty_{$size}"};
    }

    /**
     * Calculate a full shopping list for a family.
     *
     * @return array<string, array{category: string, quantity: int}>
     */
    public static function calculateForFamily(Family $family): array
    {
        $items = static::orderBy('sort_order')->orderBy('category')->orderBy('name')->get();
        $size = max($family->number_of_family_members ?? 1, 1);
        $list = [];

        foreach ($items as $item) {
            // Skip conditional items unless family qualifies
            if ($item->conditional && $item->condition_field) {
                if (!static::familyMatchesCondition($family, $item->condition_field)) {
                    continue;
                }
            }

            $qty = $item->quantityForSize($size);
            if ($qty > 0) {
                $list[$item->name] = [
                    'category' => $item->category,
                    'quantity' => $qty,
                ];
            }
        }

        return $list;
    }

    private static function familyMatchesCondition(Family $family, string $field): bool
    {
        return match ($field) {
            'needs_baby_supplies' => (bool) $family->needs_baby_supplies,
            'has_infants' => ($family->infants ?? 0) > 0,
            'has_pets' => !empty($family->pet_information),
            'has_female_adults' => ($family->female_adults ?? 0) > 0,
            default => false,
        };
    }
}
