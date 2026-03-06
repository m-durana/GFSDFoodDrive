<?php

namespace App\Models;

use App\Enums\PackingStatus;
use App\Models\Scopes\SeasonScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PackingList extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new SeasonScope);

        static::creating(function (PackingList $list) {
            if (empty($list->qr_token)) {
                $list->qr_token = (string) Str::uuid();
            }
            if (empty($list->season_year)) {
                $list->season_year = Setting::get('season_year', date('Y'));
            }
        });
    }

    protected $fillable = [
        'family_id',
        'season_year',
        'status',
        'assigned_volunteer',
        'started_at',
        'completed_at',
        'verified_by',
        'verified_at',
        'qr_token',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PackingStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingItem::class);
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_volunteer');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isComplete(): bool
    {
        return $this->items()
            ->whereNotIn('status', ['packed', 'verified', 'substituted'])
            ->doesntExist();
    }

    public function progressSummary(): array
    {
        $total = $this->items()->count();
        $packed = $this->items()
            ->whereIn('status', ['packed', 'verified', 'substituted'])
            ->count();

        return [
            'packed' => $packed,
            'total' => $total,
            'percentage' => $total > 0 ? round(($packed / $total) * 100) : 0,
        ];
    }
}
