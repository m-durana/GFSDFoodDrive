<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
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
        'driver_lat',
        'driver_lng',
        'driver_location_at',
        'returning_at',
        'completed_at',
        'route_geometry',
        'geometry_updated_at',
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
            'driver_lat' => 'decimal:7',
            'driver_lng' => 'decimal:7',
            'driver_location_at' => 'datetime',
            'returning_at' => 'datetime',
            'completed_at' => 'datetime',
            'route_geometry' => 'array',
            'geometry_updated_at' => 'datetime',
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

    public function getDisplayNameAttribute(): string
    {
        return preg_replace('/^\s*seeded\s*/i', '', $this->name);
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

    public function formattedMeta(): string
    {
        $parts = [];
        $parts[] = $this->stop_count . ' stops';
        if ($this->formattedDistance() !== '—') $parts[] = $this->formattedDistance();
        if ($this->formattedDuration() !== '—') $parts[] = $this->formattedDuration();
        return implode(' · ', $parts);
    }

    public function getRouteStatusAttribute(): string
    {
        $families = $this->families;
        if ($families->isEmpty()) return 'pending';

        $delivered = $families->where('delivery_status', DeliveryStatus::Delivered)->count();
        $total = $families->count();

        if ($this->completed_at || ($delivered === $total && $this->isNearStart())) return 'complete';
        if ($this->returning_at || $delivered === $total) return 'returning';
        if ($delivered > 0 && $delivered < $total) return 'partially_delivered';
        if ($families->where('delivery_status', DeliveryStatus::InTransit)->count() > 0) return 'in_transit';

        return 'pending';
    }

    private function isNearStart(): bool
    {
        if (! $this->driver_lat || ! $this->start_lat) return false;

        $latFrom = deg2rad((float) $this->driver_lat);
        $lngFrom = deg2rad((float) $this->driver_lng);
        $latTo = deg2rad((float) $this->start_lat);
        $lngTo = deg2rad((float) $this->start_lng);

        $dlat = $latTo - $latFrom;
        $dlng = $lngTo - $lngFrom;
        $a = sin($dlat / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($dlng / 2) ** 2;
        $km = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $km < 0.5;
    }
}
