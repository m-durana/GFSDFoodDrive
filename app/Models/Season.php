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
        'pickups_completed',
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

        return [
            'total_families' => $families->count(),
            'total_children' => $children->count(),
            'total_family_members' => (clone $families)->sum('number_of_family_members'),
            'gifts_level_0' => (clone $children)->where('gift_level', GiftLevel::None)->count(),
            'gifts_level_1' => (clone $children)->where('gift_level', GiftLevel::Partial)->count(),
            'gifts_level_2' => (clone $children)->where('gift_level', GiftLevel::Moderate)->count(),
            'gifts_level_3' => (clone $children)->where('gift_level', GiftLevel::Full)->count(),
            'deliveries_completed' => (clone $families)->where('delivery_status', DeliveryStatus::Delivered)->count(),
            'pickups_completed' => (clone $families)->where('delivery_status', DeliveryStatus::PickedUp)->count(),
            'tags_adopted' => (clone $children)->whereNotNull('adoption_token')->count(),
        ];
    }
}
