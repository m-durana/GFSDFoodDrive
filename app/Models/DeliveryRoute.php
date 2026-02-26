<?php

namespace App\Models;

use App\Models\Scopes\SeasonScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (DeliveryRoute $route) {
            if (empty($route->access_token)) {
                $route->access_token = Str::random(32);
            }
            if (empty($route->season_year)) {
                $route->season_year = Setting::get('season_year', date('Y'));
            }
        });
    }

    protected $fillable = [
        'name',
        'driver_user_id',
        'driver_name',
        'start_lat',
        'start_lng',
        'total_distance_meters',
        'total_duration_seconds',
        'stop_count',
        'access_token',
        'season_year',
    ];

    protected function casts(): array
    {
        return [
            'start_lat' => 'decimal:7',
            'start_lng' => 'decimal:7',
            'total_distance_meters' => 'integer',
            'total_duration_seconds' => 'integer',
            'stop_count' => 'integer',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class)->orderBy('route_order');
    }

    public function formattedDistance(): string
    {
        if (! $this->total_distance_meters) return '—';
        $miles = $this->total_distance_meters / 1609.34;
        return number_format($miles, 1) . ' mi';
    }

    public function formattedDuration(): string
    {
        if (! $this->total_duration_seconds) return '—';
        $minutes = round($this->total_duration_seconds / 60);
        if ($minutes < 60) return $minutes . ' min';
        return floor($minutes / 60) . 'h ' . ($minutes % 60) . 'm';
    }
}
