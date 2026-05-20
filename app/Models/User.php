<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, \App\Traits\Auditable;

    // After running `composer update`, uncomment the line below to enable Spatie roles:
    use \Spatie\Permission\Traits\HasRoles;

    protected $auditExclude = [
        'last_lat',
        'last_lng',
        'last_location_at',
    ];

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'permission',
        'is_sudoer',
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
            'is_sudoer' => 'boolean',
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
        if ($this->spatieAvailable() && $this->hasAnyRole(['coordinator', 'system_coordinator'])) {
            return true;
        }
        return $this->permission === 8;
    }

    /**
     * "System Coordinator" is the human-readable name for what the codebase
     * calls `system_coordinator` (Spatie role) / "System Engineer" (position).
     * They are the SAME identity — a Coordinator subgroup with PII access.
     * See App\Support\CoordinatorSections for the position-side comment.
     */
    public function isSystemCoordinator(): bool
    {
        if ($this->spatieAvailable() && $this->hasRole('system_coordinator')) {
            return true;
        }
        // Position-side fallback: "System Engineer" is the position name for
        // this identity. Honor it even if the Spatie role wasn't separately
        // assigned (operator forgot to also tick the role).
        if ($this->position === 'System Engineer') {
            return true;
        }
        return false;
    }

    public function isNinja(): bool
    {
        if ($this->spatieAvailable() && $this->hasRole('ninja')) {
            return true;
        }
        return false;
    }

    public function isSanta(): bool
    {
        // Per-user sudoer flag grants Santa-equivalent access (audit-logged).
        if ($this->is_sudoer) {
            return true;
        }
        // Role-level sudoer: Santa can mark a whole Spatie role as sudoer
        // via the `sudoer_roles` setting (JSON array). Every user with that
        // role then gets Santa-equivalent access until the role is untoggled.
        if ($this->roleSudoActive()) {
            return true;
        }
        if ($this->spatieAvailable() && $this->hasRole('santa')) {
            return true;
        }
        return $this->permission === 9;
    }

    /**
     * True if any of this user's Spatie roles is currently in the
     * `sudoer_roles` setting list. Cached per-request via static memo.
     */
    public function roleSudoActive(): bool
    {
        if (! $this->spatieAvailable() || ! method_exists($this, 'getRoleNames')) {
            return false;
        }
        $sudoRoles = self::sudoerRolesList();
        if (! $sudoRoles) {
            return false;
        }
        foreach ($this->getRoleNames() as $name) {
            if (in_array($name, $sudoRoles, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Static cache of the parsed sudoer_roles setting per request.
     * Returns an array of Spatie role names.
     */
    private static ?array $sudoerRolesCache = null;
    public static function sudoerRolesList(): array
    {
        if (self::$sudoerRolesCache !== null) {
            return self::$sudoerRolesCache;
        }
        $raw = \App\Models\Setting::get('sudoer_roles', null);
        if (! $raw) {
            return self::$sudoerRolesCache = [];
        }
        $decoded = json_decode((string) $raw, true);
        return self::$sudoerRolesCache = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    public static function clearSudoerRolesCache(): void
    {
        self::$sudoerRolesCache = null;
    }

    /**
     * True if this user is the "real" Santa account — full permission tier,
     * not just sudoer'd. Use this in places where you specifically need to
     * distinguish (e.g. "must-keep-one-Santa" invariants).
     */
    public function isOriginalSanta(): bool
    {
        if ($this->is_sudoer) {
            return false;
        }
        if ($this->spatieAvailable() && $this->hasRole('santa') && $this->permission >= 9) {
            return true;
        }
        return $this->permission === 9;
    }

    /**
     * True if this user is currently acting with elevated (Santa-equivalent)
     * access via either the per-user `is_sudoer` flag OR membership in a
     * Spatie role listed in the `sudoer_roles` setting. Real Santa accounts
     * (permission 9) return false — they're not sudoers, they're the actual thing.
     * Used by LogMutatingActivity to tag audit-log entries.
     */
    public function isSudoer(): bool
    {
        if ($this->permission === 9) {
            return false;
        }
        if ($this->is_sudoer) {
            return true;
        }
        return $this->roleSudoActive();
    }

    /**
     * Can this user see real names/addresses/PII?
     * Only Santa and System Coordinators by policy (see PROJECT_OVERVIEW.md §3.2).
     * Advisors get a narrower per-school exception handled at the controller layer.
     */
    public function canSeePii(): bool
    {
        return $this->isSanta() || $this->isSystemCoordinator();
    }

    /**
     * Section slugs this user is granted via their coordinator `position`,
     * per the Santa-editable `coordinator_section_map` setting. Santa +
     * System Coordinator are not represented here (they bypass section
     * checks entirely in hasCoordinatorSection()).
     */
    public function sections(): array
    {
        return \App\Support\CoordinatorSections::sectionsFor($this->position);
    }

    /**
     * True if this user is permitted to act within any of the given sections.
     * Santa + System Coordinator always pass; other users must hold a position
     * mapped to at least one of the allowed sections.
     */
    public function hasCoordinatorSection(array $allowed): bool
    {
        if ($this->isSanta() || $this->isSystemCoordinator()) {
            return true;
        }
        return (bool) array_intersect($this->sections(), $allowed);
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
        if ($this->spatieAvailable() && $this->hasAnyRole(['family', 'coordinator', 'system_coordinator', 'ninja', 'santa', 'self_service'])) {
            return true;
        }
        return $this->permission > 0;
    }
}
