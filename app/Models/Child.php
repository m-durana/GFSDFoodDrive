<?php

namespace App\Models;

use App\Enums\GiftLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'gender',
        'age',
        'school',
        'clothes_size',
        'clothing_styles',
        'clothing_options',
        'gift_preferences',
        'toy_ideas',
        'all_sizes',
        'mail_merged',
        'gifts_received',
        'gift_level',
        'where_is_tag',
        'adopter_name',
        'adopter_contact_info',
        'adopted_at',
        'adoption_token',
        'adoption_deadline',
        'gift_dropped_off',
    ];

    protected function casts(): array
    {
        return [
            'mail_merged' => 'boolean',
            'gift_level' => GiftLevel::class,
            'adopted_at' => 'datetime',
            'adoption_deadline' => 'date',
            'gift_dropped_off' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function scopeAvailableForAdoption(Builder $query): Builder
    {
        return $query->where('gift_level', GiftLevel::None->value)
            ->whereNull('adoption_token')
            ->whereHas('family', fn (Builder $q) => $q->whereNotNull('family_number'));
    }

    public function scopeAdopted(Builder $query): Builder
    {
        return $query->whereNotNull('adoption_token');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('adoption_token')
            ->where('adoption_deadline', '<', now()->toDateString())
            ->where('gift_dropped_off', false);
    }

    public function isAdopted(): bool
    {
        return $this->adoption_token !== null;
    }
}
