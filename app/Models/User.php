<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // After running `composer update`, uncomment the line below to enable Spatie roles:
    use \Spatie\Permission\Traits\HasRoles;

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'permission',
        'school_source',
        'position',
        'last_lat',
        'last_lng',
        'last_location_at',
        'show_on_website',
        'force_show_on_website',
        'avatar_path',
        'avatar_restricted',
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
            'last_lat' => 'decimal:7',
            'last_lng' => 'decimal:7',
            'last_location_at' => 'datetime',
            'show_on_website' => 'boolean',
            'force_show_on_website' => 'boolean',
            'avatar_restricted' => 'boolean',
        ];
    }

    // Relationships

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function packedItems(): HasMany
    {
        return $this->hasMany(PackingItem::class, 'packed_by');
    }

    // Role helpers - uses Spatie when available, falls back to legacy permission column

    private function spatieAvailable(): bool
    {
        return method_exists($this, 'hasRole');
    }

    public function isFamily(): bool
    {
        // Returns true for both advisor (family role) and self_service users
        if ($this->spatieAvailable() && ($this->hasRole('family') || $this->hasRole('self_service'))) {
            return true;
        }
        return $this->permission === 7 || $this->permission === 6;
    }

    public function isAdvisor(): bool
    {
        if ($this->spatieAvailable() && $this->hasRole('family')) {
            return true;
        }
        return $this->permission === 7;
    }

    public function isSelfService(): bool
    {
        if ($this->spatieAvailable() && $this->hasRole('self_service')) {
            return true;
        }
        return $this->permission === 6;
    }

    public function isCoordinator(): bool
    {
        if ($this->spatieAvailable() && $this->hasRole('coordinator')) {
            return true;
        }
        return $this->permission === 8;
    }

    public function isSanta(): bool
    {
        if ($this->spatieAvailable() && $this->hasRole('santa')) {
            return true;
        }
        return $this->permission === 9;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path && str_starts_with($this->avatar_path, 'dicebear:')) {
            $seed = urlencode(substr($this->avatar_path, 9));
            return "https://api.dicebear.com/9.x/notionists-neutral/svg?seed={$seed}";
        }
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        $seed = urlencode($this->username ?? $this->first_name . $this->last_name);
        return "https://api.dicebear.com/9.x/notionists-neutral/svg?seed={$seed}";
    }

    public function isActive(): bool
    {
        if ($this->spatieAvailable() && $this->hasAnyRole(['family', 'coordinator', 'santa', 'self_service'])) {
            return true;
        }
        return $this->permission > 0;
    }
}
