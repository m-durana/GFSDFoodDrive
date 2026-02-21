<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'permission',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permission' => 'integer',
        ];
    }

    // Relationships

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    // Role helpers - now using Spatie roles with fallback to legacy permission column

    public function isFamily(): bool
    {
        return $this->hasRole('family') || $this->permission === 7;
    }

    public function isCoordinator(): bool
    {
        return $this->hasRole('coordinator') || $this->permission === 8;
    }

    public function isSanta(): bool
    {
        return $this->hasRole('santa') || $this->permission === 9;
    }

    public function isActive(): bool
    {
        return $this->hasAnyRole(['family', 'coordinator', 'santa']) || $this->permission > 0;
    }
}
