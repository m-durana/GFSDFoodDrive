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
        'gifts_level_0',
        'gifts_level_1',
        'gifts_level_2',
        'gifts_level_3',
        'deliveries_completed',
        'tags_adopted',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
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
}
