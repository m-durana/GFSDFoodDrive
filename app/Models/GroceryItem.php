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
    ];

    protected function casts(): array
    {
        return [
            'conditional' => 'boolean',
        ];
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
