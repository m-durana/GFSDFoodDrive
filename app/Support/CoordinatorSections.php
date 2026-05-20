<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Single source of truth for Coordinator section slugs + the
 * position→sections permission map.
 *
 * Sections are deliberately a fixed, small list. The position→sections
 * map is editable by Santa via the Settings UI (key: coordinator_section_map,
 * stored as JSON) so responsibilities can shift year-to-year without a
 * code change.
 *
 * Santa + System Coordinator always pass every section check (see
 * App\Http\Middleware\CoordinatorSection); this map only governs regular
 * Coordinators.
 */
class CoordinatorSections
{
    /**
     * Canonical sections. Slug → human label.
     *
     * Keep this list short. If a new area needs gating, add it here AND
     * apply `section:<slug>` middleware to the relevant route group.
     */
    public const SECTIONS = [
        'giving-tree' => 'Giving Tree (adoptions, gift bank, gift drop-off, gift intake)',
        'food'        => 'Food (food warehouse, kiosk, inventory, shopping)',
        'packing'     => 'Packing (per-family packing lists, scanner, verify station)',
        'delivery'    => 'Delivery (dispatch map, route building, driver assignment)',
        'business'    => 'Business / Marketing / Media (seasons, reports, backups, analytics, marketing, video)',
        'system'      => 'System (operator-only: coordinator PDF generators, advanced settings)',
    ];

    /**
     * Default position → list of section slugs. Used when Santa hasn't
     * customised `coordinator_section_map` in Settings yet. Positions not
     * present here grant no section access (the user can still log in but
     * sees nothing section-gated).
     *
     * NOTE on naming: the position "System Engineer" is the SAME identity
     * as the Spatie role `system_coordinator`. A user assigned the
     * "System Engineer" position should also hold the `system_coordinator`
     * Spatie role (so they get PII access via User::canSeePii()). Don't
     * introduce a parallel "System Coordinator" position — they're the
     * same thing under two different names.
     */
    public const DEFAULT_MAP = [
        'System Engineer'           => ['giving-tree', 'food', 'packing', 'delivery', 'business', 'system'],
        'Giving Tree Coordinator'   => ['giving-tree'],
        'Food Manager'              => ['food', 'packing'],
        'Activities Coordinator'    => ['business'],
        'Business Operator'         => ['business'],
        // Marketing/Media positions (REL-46b: media merged into business).
        'Video Producer'            => ['business'],
        'Marketing Director'        => ['business'],
    ];

    /**
     * Return the operator-edited position→sections map, falling back to
     * DEFAULT_MAP entries for positions that aren't in the override.
     */
    public static function map(): array
    {
        $raw = Setting::get('coordinator_section_map', null);
        if (! $raw) {
            return self::DEFAULT_MAP;
        }

        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return self::DEFAULT_MAP;
        }

        return $decoded + self::DEFAULT_MAP;
    }

    /**
     * Sections granted to a given coordinator position. Returns [] if the
     * position is unknown.
     */
    public static function sectionsFor(?string $position): array
    {
        if (! $position) {
            return [];
        }
        $map = self::map();
        $sections = $map[$position] ?? [];
        return is_array($sections) ? array_values(array_intersect($sections, array_keys(self::SECTIONS))) : [];
    }
}
