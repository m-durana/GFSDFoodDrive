<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingSession extends Model
{
    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'items_packed',
        'lists_worked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'items_packed' => 'integer',
            'lists_worked' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    public function durationInHours(): float
    {
        $end = $this->ended_at ?? now();
        $minutes = $this->started_at->diffInMinutes($end);

        return max($minutes / 60, 0.01);
    }

    public function itemsPerHour(): float
    {
        $hours = $this->durationInHours();

        return $hours > 0 ? round($this->items_packed / $hours, 1) : 0;
    }

    public static function activeFor(User $user): ?self
    {
        return static::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}
