<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\GiftLevel;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = [
        'year',
        'total_families',
        'total_children',
        'total_family_members',
        'total_adults',
        'gifts_level_0',
        'gifts_level_1',
        'gifts_level_2',
        'gifts_level_3',
        'deliveries_completed',
        'pickups_completed',
        'tags_adopted',
        'families_severe_need',
        'families_with_pets',
        'families_needing_baby_supplies',
        'children_by_age_group',
        'families_by_school',
        'families_by_size',
        'families_by_language',
        'families_by_delivery_date',
        'warehouse_stats',
        'avg_family_size',
        'avg_children_per_family',
        'adoption_rate',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'children_by_age_group' => 'array',
            'families_by_school' => 'array',
            'families_by_size' => 'array',
            'families_by_language' => 'array',
            'families_by_delivery_date' => 'array',
            'warehouse_stats' => 'array',
        ];
    }

    public function families()
    {
        return Family::withoutGlobalScopes()->where('season_year', $this->year);
    }

    public function children()
    {
        return Child::withoutGlobalScopes()->where('season_year', $this->year);
    }

    public static function computeStats(int $year): array
    {
        $families = Family::withoutGlobalScopes()->where('season_year', $year);
        $children = Child::withoutGlobalScopes()->where('season_year', $year);

        $totalFamilies = $families->count();
        $totalChildren = $children->count();
        $tagsAdopted = (clone $children)->whereNotNull('adoption_token')->count();

        // Delivery status breakdown
        $familiesByDeliveryStatus = [];
        foreach (DeliveryStatus::cases() as $status) {
            $familiesByDeliveryStatus[$status->value] = (clone $families)->where('delivery_status', $status)->count();
        }

        // Families by delivery date
        $familiesByDeliveryDate = (clone $families)
            ->whereNotNull('delivery_date')
            ->selectRaw('delivery_date, count(*) as count')
            ->groupBy('delivery_date')
            ->orderBy('delivery_date')
            ->pluck('count', 'delivery_date')
            ->toArray();

        // Families by language
        $familiesByLanguage = (clone $families)
            ->whereNotNull('preferred_language')
            ->where('preferred_language', '!=', '')
            ->selectRaw('preferred_language, count(*) as count')
            ->groupBy('preferred_language')
            ->orderByDesc('count')
            ->pluck('count', 'preferred_language')
            ->toArray();

        // Warehouse items for this season year
        $totalWarehouseItems = WarehouseTransaction::withoutGlobalScopes()
            ->where('season_year', $year)
            ->where('transaction_type', 'in')
            ->sum('quantity');

        return [
            'total_families' => $totalFamilies,
            'total_children' => $totalChildren,
            'total_family_members' => (clone $families)->sum('number_of_family_members'),
            'total_adults' => (clone $families)->sum('number_of_adults'),
            'avg_family_size' => $totalFamilies > 0 ? round((clone $families)->avg('number_of_family_members'), 1) : 0,
            'avg_children_per_family' => $totalFamilies > 0 ? round((clone $families)->avg('number_of_children'), 1) : 0,
            'gifts_level_0' => (clone $children)->where('gift_level', GiftLevel::None)->count(),
            'gifts_level_1' => (clone $children)->where('gift_level', GiftLevel::Partial)->count(),
            'gifts_level_2' => (clone $children)->where('gift_level', GiftLevel::Moderate)->count(),
            'gifts_level_3' => (clone $children)->where('gift_level', GiftLevel::Full)->count(),
            'deliveries_completed' => (clone $families)->where('delivery_status', DeliveryStatus::Delivered)->count(),
            'tags_adopted' => $tagsAdopted,
            'adoption_rate' => $totalChildren > 0 ? round(($tagsAdopted / $totalChildren) * 100, 1) : 0,
            'families_by_delivery_status' => $familiesByDeliveryStatus,
            'families_by_delivery_date' => $familiesByDeliveryDate,
            'families_needing_baby_supplies' => (clone $families)->where('needs_baby_supplies', true)->count(),
            'families_with_pets' => (clone $families)->whereNotNull('pet_information')->where('pet_information', '!=', '')->count(),
            'families_by_language' => $familiesByLanguage,
            'total_warehouse_items' => (int) $totalWarehouseItems,
        ];
    }

    /**
     * Compute detailed stats for analytics — richer breakdowns for archiving.
     */
    public static function computeDetailedStats(int $year): array
    {
        $base = static::computeStats($year);

        $families = Family::withoutGlobalScopes()->where('season_year', $year);
        $children = Child::withoutGlobalScopes()->where('season_year', $year);

        // Children by age group
        $childrenByAgeGroup = [
            'Infants (0-2)' => (clone $families)->sum('infants'),
            'Young (3-7)' => (clone $families)->sum('young_children'),
            'Children (8-12)' => (clone $families)->sum('children_count'),
            'Tweens (13-14)' => (clone $families)->sum('tweens'),
            'Teens (15-17)' => (clone $families)->sum('teenagers'),
        ];

        // Families by school (based on children's schools)
        $familiesBySchool = (clone $children)
            ->whereNotNull('school')
            ->where('school', '!=', '')
            ->selectRaw('school, count(distinct family_id) as family_count')
            ->groupBy('school')
            ->orderByDesc('family_count')
            ->pluck('family_count', 'school')
            ->toArray();

        // Family size distribution
        $familiesBySize = [
            '1-2' => (clone $families)->whereBetween('number_of_family_members', [1, 2])->count(),
            '3-4' => (clone $families)->whereBetween('number_of_family_members', [3, 4])->count(),
            '5-6' => (clone $families)->whereBetween('number_of_family_members', [5, 6])->count(),
            '7+' => (clone $families)->where('number_of_family_members', '>=', 7)->count(),
        ];

        // Severe need families
        $familiesSevereNeed = (clone $families)->where('is_severe_need', true)->count();

        // Pickup vs delivery
        $pickupsCompleted = (clone $families)
            ->where(function ($q) {
                $q->where('delivery_preference', 'Pickup')
                    ->orWhere('delivery_preference', 'pickup');
            })
            ->where('delivery_status', DeliveryStatus::Delivered)
            ->count();

        // Warehouse category breakdown
        $warehouseStats = [];
        try {
            $warehouseStats = WarehouseTransaction::withoutGlobalScopes()
                ->where('season_year', $year)
                ->where('transaction_type', 'in')
                ->join('warehouse_items', 'warehouse_transactions.warehouse_item_id', '=', 'warehouse_items.id')
                ->join('warehouse_categories', 'warehouse_items.category_id', '=', 'warehouse_categories.id')
                ->selectRaw('warehouse_categories.type, sum(warehouse_transactions.quantity) as total')
                ->groupBy('warehouse_categories.type')
                ->pluck('total', 'type')
                ->toArray();
        } catch (\Exception $e) {
            // Warehouse tables may not exist in older seasons
        }

        return array_merge($base, [
            'children_by_age_group' => $childrenByAgeGroup,
            'families_by_school' => $familiesBySchool,
            'families_by_size' => $familiesBySize,
            'families_severe_need' => $familiesSevereNeed,
            'pickups_completed' => $pickupsCompleted,
            'families_with_pets' => $base['families_with_pets'],
            'families_needing_baby_supplies' => $base['families_needing_baby_supplies'],
            'warehouse_stats' => $warehouseStats,
        ]);
    }
}
