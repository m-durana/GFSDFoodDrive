<?php

namespace App\Models;

use App\Models\Scopes\SeasonScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryTeam extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (DeliveryTeam $team) {
            if (empty($team->season_year)) {
                $team->season_year = Setting::get('season_year', date('Y'));
            }
        });
    }

    protected $fillable = [
        'name',
        'color',
        'driver_user_id',
        'driver_name',
        'notes',
        'season_year',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function getDriverDisplayName(): string
    {
        if ($this->driver) {
            return $this->driver->first_name;
        }

        if ($this->driver_name) {
            return $this->driver_name;
        }

        return 'Unassigned';
    }
}
