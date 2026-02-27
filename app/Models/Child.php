<?php

namespace App\Models;

use App\Enums\GiftLevel;
use App\Models\Scopes\SeasonScope;
use App\Observers\ChildObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ChildObserver::class)]
class Child extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (Child $child) {
            if (empty($child->season_year)) {
                $family = Family::withoutGlobalScopes()->find($child->family_id);
                $child->season_year = $family?->season_year ?? Setting::get('season_year', date('Y'));
            }
        });
    }

    protected $fillable = [
        'family_id',
        'season_year',
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
        'adopter_email',
        'adopter_phone',
        'adopted_at',
        'adoption_token',
        'adoption_deadline',
        'gift_dropped_off',
        'adoption_reminder_sent',
    ];

    protected function casts(): array
    {
        return [
            'mail_merged' => 'boolean',
            'gift_level' => GiftLevel::class,
            'adopted_at' => 'datetime',
            'adoption_deadline' => 'date',
            'gift_dropped_off' => 'boolean',
            'adoption_reminder_sent' => 'boolean',
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

    public function scopeNotMailMerged(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('mail_merged', false)->orWhereNull('mail_merged');
        });
    }

    public function scopeWithGiftLevel(Builder $query, GiftLevel $level): Builder
    {
        return $query->where('gift_level', $level->value);
    }

    public function scopeWithoutGifts(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('gift_level', GiftLevel::None->value)->orWhereNull('gift_level');
        });
    }
}
