<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessRequest extends Model
{
    protected $fillable = [
        'email',
        'name',
        'google_id',
        'avatar',
        'requested_role',
        'school_source',
        'position',
        'status',
        'reviewed_by',
        'deny_reason',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function roleLabel(): string
    {
        return match ($this->requested_role) {
            'family' => 'Family / Advisor',
            'coordinator' => 'Coordinator',
            'santa' => 'Santa (Admin)',
            default => ucfirst($this->requested_role),
        };
    }
}
