# GFSD Food Drive — Feature Roadmap

> **Created:** 2026-02-23
> **Context:** After 8 phases of core development, the app handles family registration, gift tracking, document generation, shopping day coordination, and delivery logistics. These next features transform it from an internal admin tool into a community-facing platform.

---

## Feature 1: Digital Adopt-a-Tag Portal

### The Problem
Gift tags are printed on paper and hung on a physical tree. Community members who happen to walk by can pick one up. This limits gift adoption to foot traffic at the school — a tiny fraction of the community.

### The Vision
A **public webpage** where anyone with the link can browse unclaimed gift tags, claim one, and get reminders. The coordinator sees real-time adoption status. The link can be shared on Facebook, school newsletters, Nextdoor, church bulletins — reaching the entire Granite Falls community.

### User Stories
- **Community member** visits `/adopt` → sees a grid of unclaimed tags (anonymized: "Girl, age 7 — likes dinosaurs and Legos") → clicks "I'll adopt this tag" → enters name + email/phone → gets a confirmation with deadline
- **Community member** gets an email/text reminder 3 days before the deadline if they haven't marked the gift as purchased
- **Community member** can mark a tag as "Gift purchased and dropped off" from their confirmation link
- **Coordinator** sees a live dashboard: X/Y tags adopted, who adopted what, which are overdue
- **Santa** can manually assign or unassign adopters, set the deadline, enable/disable the portal

### Schema Changes
Uses existing columns on `children` table:
- `adopter_name` — already exists
- `adopter_contact_info` — already exists
- `gift_level` — already exists (0=none → 3=full)

New columns on `children`:
```
adopted_at          TIMESTAMP NULLABLE   — when someone claimed this tag
adoption_token      VARCHAR(32) UNIQUE   — for the adopter's private link
adoption_deadline   DATE NULLABLE        — when the gift should be dropped off
gift_dropped_off    BOOLEAN DEFAULT FALSE — adopter self-reports
```

New settings:
```
adopt_a_tag_enabled    '0'/'1'   — toggle portal on/off
adopt_a_tag_deadline   DATE      — default deadline for new adoptions
adopt_a_tag_message    TEXT      — custom message shown on the portal
```

### Routes
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/adopt` | Public | Browse available tags (grid view) |
| GET | `/adopt/{child}` | Public | Single tag detail + claim form |
| POST | `/adopt/{child}/claim` | Public | Claim a tag (name + contact) |
| GET | `/adopt/mine/{token}` | Public | Adopter's confirmation page |
| POST | `/adopt/mine/{token}/delivered` | Public | Mark gift as dropped off |
| GET | `/santa/adoptions` | Santa | Admin dashboard for all adoptions |

### Key Design Decisions
- **Anonymized tags** — No family names or addresses shown. Only: gender, age, interests, clothing sizes, toy ideas. Family number shown but means nothing to the public.
- **No login required** — Adopters just enter name + email/phone. They get a token link for their confirmation page.
- **One claim per tag** — Once claimed, the tag shows "Adopted" with a heart icon. If the adopter flakes (past deadline, not dropped off), the coordinator can release it back.
- **Rate limiting** — Limit claims per IP/session to prevent one person hoarding all tags.
- **Gift level auto-update** — When a tag is claimed, set `gift_level` to 1 (partial). When dropped off, set to 2 (moderate). Coordinator can upgrade to 3 (full) manually.

### Files to Create/Modify
| File | Action |
|------|--------|
| `database/migrations/xxx_add_adoption_columns.php` | New — add columns to children |
| `app/Http/Controllers/AdoptionController.php` | New — public portal + admin dashboard |
| `resources/views/adopt/index.blade.php` | New — public tag grid |
| `resources/views/adopt/show.blade.php` | New — single tag + claim form |
| `resources/views/adopt/confirmation.blade.php` | New — adopter's private page |
| `resources/views/santa/adoptions.blade.php` | New — admin adoption dashboard |
| `resources/views/santa/index.blade.php` | Modify — add to Gifts & Shopping section |
| `resources/views/santa/settings.blade.php` | Modify — add portal toggle + deadline |
| `routes/web.php` | Modify — add public adopt routes + santa adoption route |
| `app/Models/Child.php` | Modify — add adoption scopes and helpers |

### Complexity: Medium
- Mostly CRUD + public views with token auth
- The grid UI with filtering is the most work
- Email/SMS reminders are optional for v1 (can add later)

---

## Feature 2: Family Status Page

### The Problem
Families register and then hear nothing. They don't know if gifts were adopted for their kids, when delivery is coming, or what's happening. This creates anxiety and generates phone calls to coordinators.

### The Vision
Each family gets a **private link** (no login required) where they can see their status in real time. "Your family is registered" → "Gifts are being collected for your children" → "Your delivery is scheduled for Dec 19" → "Out for delivery!" → "Delivered."

### User Stories
- **Family** receives a text/email with their private status link when they register
- **Family** visits `/family/status/{token}` → sees a clean, mobile-friendly timeline of their status
- **Family** can see which of their children have gifts being collected (without revealing adopter info)
- **Family** gets a text when delivery status changes to "In Transit"
- **Coordinator** can regenerate/resend a family's status link from the family detail page

### Schema Changes
New columns on `families`:
```
status_token    VARCHAR(32) UNIQUE   — for the family's private status link
```

### Routes
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/family/status/{token}` | Public | Family status page |
| POST | `/family/{family}/send-status-link` | Coordinator+ | Send/resend status link |

### What the Status Page Shows
1. **Family registered** — always shown, with date
2. **Children's gift status** — "Gifts are being collected for [N] of your [M] children" (aggregated, no adopter details)
3. **Delivery info** — If delivery_team assigned: "Your delivery is scheduled." If delivery_status = in_transit: "Your gifts are on the way!" If delivered: "Your gifts have been delivered."
4. **Contact info** — "Questions? Call [coordinator phone] or email [email]"

### Design
- Mobile-first, standalone page (not app layout)
- Simple vertical timeline with status dots
- Green checkmarks for completed steps
- Animated pulse on current step
- No login, no family details exposed (just first name)

### Complexity: Easy
- Single read-only page with a token lookup
- Status derived from existing `delivery_status` + children's `gift_level`
- The SMS integration is the only complex part (defer to Feature 5)

---

## Feature 3: Season Archive & Historical Data

### The Problem
The food drive has run since at least 2016. Data from previous years exists in Access databases (`.accdb` files) but is inaccessible. There's no way to start a new season without manually wiping the database. Historical metrics (how many families, children, gifts per year) are lost.

### The Vision
- **"Start New Season"** button archives current data and resets for next year
- **Historical dashboard** shows year-over-year trends: families served, children served, gifts adopted, delivery stats
- **Import legacy data** from the Access database exports (2016-2020 Excel backups already exist in `.claude/Database 2019/` and `.claude/Database 2020/`)

### Schema Changes
New table: `seasons`
```
id
year                SMALLINT UNIQUE
families_count      INT
children_count      INT
gifts_adopted       INT
gifts_delivered     INT
total_volunteers    INT
notes               TEXT NULLABLE
archived_at         TIMESTAMP NULLABLE
created_at / updated_at
```

New table: `season_snapshots` (denormalized archive)
```
id
season_id           FK → seasons
family_data         JSON   — full family record at time of archive
children_data       JSON   — all children for the family
created_at
```

New column on existing tables:
```
families.season_year    SMALLINT DEFAULT current_year
children.season_year    SMALLINT DEFAULT current_year
```

### Season Rollover Flow
1. Santa clicks "Archive & Start New Season" on Settings page
2. System creates a `season` record with aggregate stats
3. System copies each family + children into `season_snapshots` as JSON
4. System sets all families/children `season_year` to the old year
5. System increments the `season_year` setting
6. Active views filter by `WHERE season_year = current_season` by default
7. A "View Past Seasons" page shows archived data

### Legacy Import Flow
1. Santa uploads an Excel file (`.xlsx`) from the Access database backups
2. System maps columns (Family Name, Address, Phone, etc.) — the 2019/2020 backups have known column layouts
3. System creates `season` + `season_snapshots` records for the historical year
4. Historical data appears in the year-over-year charts

### Routes
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/santa/seasons` | Santa | Historical seasons dashboard |
| GET | `/santa/seasons/{year}` | Santa | Detail view for one season |
| POST | `/santa/seasons/archive` | Santa | Archive current season |
| POST | `/santa/seasons/import` | Santa | Import legacy Excel data |

### Historical Dashboard Metrics
- **Year-over-year chart**: families served, children served, gifts per child
- **Per-season breakdown**: families by school, age distribution, gift level distribution
- **Delivery stats**: on-time rate, delivery method split (delivery vs pickup)
- **Growth trends**: new families vs returning families (matched by name/address)

### Complexity: Medium-Hard
- Season rollover itself is straightforward (copy + filter)
- Legacy import requires mapping Access/Excel columns to the current schema
- The year-over-year charts need a charting library (Chart.js via CDN is fine)
- Returning family detection (fuzzy name/address matching) is medium complexity

### Files to Create/Modify
| File | Action |
|------|--------|
| `database/migrations/xxx_create_seasons_tables.php` | New |
| `database/migrations/xxx_add_season_year_to_families.php` | New |
| `app/Models/Season.php` | New |
| `app/Models/SeasonSnapshot.php` | New |
| `app/Http/Controllers/SeasonController.php` | New |
| `resources/views/santa/seasons/index.blade.php` | New — dashboard with charts |
| `resources/views/santa/seasons/show.blade.php` | New — single season detail |
| `resources/views/santa/settings.blade.php` | Modify — add archive button |
| `app/Services/LegacyImporter.php` | New — Excel parsing service |
| Global query scopes on Family/Child models | Modify — filter by season_year |

---

## Feature 4: Route-Optimized Delivery ("Uber for Delivery Day")

### The Problem
Delivery drivers currently get a list of families and figure out their own route. With 75 families and 5-10 drivers, this leads to inefficient routes, wasted gas, and late deliveries.

### The Vision
Santa clicks "Optimize Routes" → the system calls the OpenRouteService API → each driver gets an optimized route with ordered stops and one-tap Google Maps navigation.

### Technical Approach
- **API**: OpenRouteService `/v2/optimization` (wraps VROOM solver) — free, REST-based
- **Input**: Driver start locations + all family lat/lng coordinates
- **Output**: Ordered stops per driver with estimated distance/duration
- **Navigation**: Google Maps URL deep links for each stop
- **Live tracking**: Already built (Leaflet map + geolocation)

### Schema Changes
New table: `delivery_routes`
```
id
name                    VARCHAR — "Route A", "Team Red"
driver_user_id          FK NULLABLE → users
driver_name             VARCHAR NULLABLE — for non-account drivers
start_lat / start_lng   DECIMAL(10,7)
total_distance_meters   INT
total_duration_seconds  INT
stop_count              SMALLINT
season_year             SMALLINT
timestamps
```

New column on `families`:
```
route_order     SMALLINT NULLABLE   — position within driver's route
delivery_route_id  FK NULLABLE → delivery_routes
```

### Routes
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/santa/delivery-routes` | Santa | Route management page |
| POST | `/santa/delivery-routes/optimize` | Santa | Call ORS API, generate routes |
| GET | `/delivery/route/{token}` | Public | Driver's mobile route view |
| POST | `/delivery/route/{token}/complete/{family}` | Public | Mark stop as delivered |

### Complexity: Medium
- ORS API integration is a single HTTP POST (easy)
- The driver route view is a new mobile-optimized page (easy)
- Free tier limit of 3 vehicles/request means batching for >3 drivers (easy workaround)
- Route polylines on the existing map is medium

### Cost: $0
- OpenRouteService free tier: 500 requests/day (we need 1-3)
- Google Maps navigation links: free (URL scheme, no API key)
- Everything else: already built

---

## Feature 5: SMS Notifications (Future)

### The Problem
Families have no way to know delivery is coming. Drivers sometimes can't reach families. Coordinators play phone tag.

### The Vision
Automated texts at key moments:
- Family registration confirmation: "You're registered for the GFSD Food Drive!"
- Gifts adopted: "Great news — gifts are being collected for your children!"
- Delivery coming: "Your gifts are on the way! Expected in ~20 minutes."
- Delivery complete: "Your gifts have been delivered. Happy holidays!"

### Technical Approach
- **Twilio** or **Vonage** for SMS — ~$0.0075/message
- 75 families x 4 messages = 300 messages/season = ~$2.25/year
- Triggered by status changes in the existing delivery workflow

### Complexity: Easy (once API key is configured)
### Cost: ~$2-5/year + Twilio account setup

### Dependency
- Requires Feature 2 (Family Status Page) for the status link included in texts
- Optional — the other features work without SMS

---

## Feature 6: Command Center Dashboard (Future)

### The Problem
During shopping day and delivery day, someone is coordinating everything from a "war room." They need at-a-glance visibility across all operations.

### The Vision
A full-screen auto-refreshing dashboard designed for a big TV screen:
- **Shopping mode**: items checked off across all NINJA assignments, active shoppers, completion %
- **Delivery mode**: families delivered/pending/in-transit, driver positions on map, ETA estimates
- Switches between modes based on the current event

### Complexity: Medium
- Mostly aggregation queries + auto-refresh
- Could reuse existing Leaflet map component
- Chart.js for live progress bars

---

## Implementation Priority

| Priority | Feature | Impact | Effort | Dependencies |
|----------|---------|--------|--------|--------------|
| 1 | Adopt-a-Tag Portal | Very High — directly increases gifts for children | Medium | None |
| 2 | Family Status Page | High — dignity + reduces coordinator workload | Easy | None |
| 3 | Season Archive | High — enables year-over-year learning | Medium-Hard | None |
| 4 | Delivery Routes | Medium — efficiency for ~1 day/year | Medium | Geocoding (done) |
| 5 | SMS Notifications | Medium — convenience | Easy | Feature 2 |
| 6 | Command Center | Low — nice-to-have | Medium | Features 1-4 |

### Recommended implementation order: 1 → 2 → 3 → 4

Features 1 and 2 are independent and could be built in parallel. Feature 3 should come before the next season starts. Feature 4 enhances the existing delivery day workflow.

---

## Legacy Database Files Available for Import

The `.claude/` directory contains Access database exports from 2019 and 2020:

**2019 Data:**
- `Database 2019/Data Back-Ups/` — Daily child + family table Excel exports from 11/15 through 12/19
- `Database 2019/FoodDriveDatabase_be 2019 v1.1.accdb` — Backend database
- `Database 2019/FoodDriveSurvey_fe Admin.accdb` — Frontend/survey database
- Column mappings known from the mail merge templates

**2020 Data:**
- `Database 2020/Backups/` — Daily exports from 11/4 through 12/17
- Follows same schema as 2019

These can be parsed with `PhpSpreadsheet` (already available via Laravel's ecosystem) for the Feature 3 legacy import.
