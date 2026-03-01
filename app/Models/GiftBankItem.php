<?php

namespace App\Models;

use App\Models\Scopes\SeasonScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftBankItem extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (GiftBankItem $item) {
            if (empty($item->season_year)) {
                $item->season_year = Setting::get('season_year', date('Y'));
            }
        });
    }

    protected $fillable = [
        'season_year',
        'description',
        'age_range',
        'gender_suitability',
        'gift_type',
        'donor_name',
        'quantity',
        'assigned_child_id',
        'assigned_at',
        'received_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'quantity' => 'integer',
            'season_year' => 'integer',
        ];
    }

    public function assignedChild(): BelongsTo
    {
        return $this->belongsTo(Child::class, 'assigned_child_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_child_id');
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereNotNull('assigned_child_id');
    }

    public function scopeForAgeRange(Builder $query, string $range): Builder
    {
        return $query->where(function ($q) use ($range) {
            $q->where('age_range', $range)->orWhere('age_range', 'any')->orWhereNull('age_range');
        });
    }

    public function scopeForGender(Builder $query, string $gender): Builder
    {
        return $query->where(function ($q) use ($gender) {
            $q->where('gender_suitability', $gender)
              ->orWhere('gender_suitability', 'neutral')
              ->orWhereNull('gender_suitability');
        });
    }
}
