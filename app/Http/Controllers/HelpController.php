<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        return view('help.index');
    }

    public function show(string $topic): View
    {
        $topics = self::topics();
        $current = collect($topics)->firstWhere('slug', $topic);

        if (!$current) {
            abort(404);
        }

        return view('help.show', compact('current', 'topics'));
    }

    public static function topics(): array
    {
        return [
            [
                'slug' => 'getting-started',
                'title' => 'Getting Started',
                'icon' => 'rocket',
                'role' => 'all',
                'content' => <<<'MD'
## Getting Started

Welcome to the GFSD Food & Gift Drive management system! This guide will help you understand the basics.

### Logging In
- Visit the site and click **Staff Login**
- Enter your username and password (provided by a Santa/admin)
- If Google OAuth is enabled, you can sign in with your Google account

### Your Dashboard
After logging in, you'll be redirected to your role-appropriate dashboard:
- **Family role**: View and manage your assigned families
- **Coordinator role**: Access gift tags, family summaries, delivery sheets
- **Santa role**: Full admin access to all features

### Navigation
Use the top navigation bar to access different sections. The sidebar on mobile can be toggled with the hamburger menu.
MD
            ],
            [
                'slug' => 'family-management',
                'title' => 'Managing Families',
                'icon' => 'users',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Managing Families

### Adding a Family
1. Go to **Family > Add Family**
2. Fill in the family name, address, phone numbers
3. Add the number of adults and children
4. Set delivery preference (Delivery or Pickup)
5. Click **Save**

### Family Numbers
Family numbers are assigned by Santa via **Number Assignment**. Numbers can be auto-assigned or manually set.

### Adding Children
On the family detail page, scroll to **Children** and click **Add Child**. Enter the child's age, gender, clothing sizes, and gift preferences. This information appears on the gift tags.

### Self-Registration
When enabled in settings, families can register themselves at the public registration URL. Their submissions appear in the family list for review.
MD
            ],
            [
                'slug' => 'gift-tags',
                'title' => 'Gift Tags & Printing',
                'icon' => 'tag',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Gift Tags

### Printing Gift Tags
1. Go to **Coordinator > Gift Tags**
2. Choose a filter: **Unmerged** (not yet printed), **All**, or a **family number range**
3. Optionally check **Mark as merged** to track which tags have been printed
4. Click **Generate** — tags open as a PDF (or HTML if DomPDF isn't installed)

### Tag Layout
- 10 tags per page (5 rows x 2 columns)
- Each tag shows: family number, child's gender, age, clothing sizes, toy ideas, gift preferences
- QR code links to the Adopt-a-Tag page (when enabled) for digital claiming
- Footer includes the adopt-a-tag deadline when set

### Adopt-a-Tag
When enabled, community members can browse available tags online and claim them. The deadline is set in **Settings > Adopt-a-Tag**.
MD
            ],
            [
                'slug' => 'delivery-day',
                'title' => 'Delivery Day',
                'icon' => 'truck',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Delivery Day

### Dispatch Board
The main delivery page has three tabs:
- **Dispatch Board**: View all routes with families, update delivery status inline
- **Route Builder**: Create routes, assign families, optimize with OpenRouteService
- **Teams**: Create delivery teams with colors and drivers

### Updating Delivery Status
On the dispatch board, use the dropdown next to each family to change status:
- **Pending** → **In Transit** → **Delivered** or **Picked Up**
- Changes happen via AJAX (no page reload needed)

### Live Map
Click **Live Map** to see all families on a map color-coded by status. The map shows:
- Route polylines connecting stops
- Volunteer/driver locations (when sharing)
- Click any marker to see family details and mark as delivered

### Driver View
Each route has a **Driver View** link — a mobile-friendly page that drivers use on their phones. It shows:
- Route stops in order with navigation links
- Progress bar
- One-tap "Delivered" button
- Auto-updates every 15 seconds

### Location Sharing
Volunteers can share their location by clicking **Share Location**. Their position appears on the live map and command center.
MD
            ],
            [
                'slug' => 'shopping',
                'title' => 'Shopping Lists',
                'icon' => 'cart',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Shopping Lists

### Grocery Formula
In **Shopping List**, add grocery items with a formula. Each item has:
- **Name** and **description**
- **Base quantity** per family
- **Per-person multiplier**

The system calculates total quantities needed based on family sizes.

### Shopping Day
On **Shopping Day**, create assignments linking NINJAs (volunteers) to specific items. Each assignment gets a unique URL that volunteers can access on their phones to check off items as they shop.

### NINJA Progress
The Command Center shows real-time NINJA progress bars — how many items each volunteer has checked off.
MD
            ],
            [
                'slug' => 'command-center',
                'title' => 'Command Center',
                'icon' => 'monitor',
                'role' => 'santa',
                'content' => <<<'MD'
## Command Center

The Command Center is a full-screen, auto-refreshing dashboard designed to be displayed on a TV during operations.

### Modes
- **Overview**: Total families, children, members, gift coverage charts
- **Shopping**: NINJA progress bars, items checked/remaining
- **Delivery**: Live map with driver locations, route progress, activity feed

The dashboard refreshes every 15 seconds automatically.

### Best Practices
- Display on a large screen/TV in the operations room
- Switch modes based on the current phase (shopping day vs. delivery day)
- The delivery mode map shows real-time driver positions when volunteers share their location
MD
            ],
            [
                'slug' => 'settings',
                'title' => 'Admin Settings',
                'icon' => 'cog',
                'role' => 'santa',
                'content' => <<<'MD'
## Admin Settings

Settings are organized into sections accessible via the sidebar:

### Public Features
- **Self-Registration**: Enable/disable family self-service registration
- **Adopt-a-Tag**: Enable the public gift tag adoption portal, set deadline and custom message
- **Family Status Pages**: Allow families to check their status via a unique link

### Operations
- **Delivery Dates**: Set the delivery dates (shown on forms and documents)
- **Delivery Time Range**: Set the allowed delivery time window
- **Season Year**: Controls which data is visible (multi-year archive support)
- **Coordinator Positions**: Define available positions for the coordinator team

### Branding
- **Site Logo**: Upload this year's logo (appears on homepage and as favicon)
- **Sponsor Logos**: Upload sponsor logos for the homepage sponsors section

### Notifications
- **Email Notifications**: Toggle email sending
- **SMS (Twilio)**: Configure Twilio credentials and which events trigger SMS

### Integrations
- **Google OAuth**: Set up Google sign-in for staff
- **OpenRouteService**: API key for delivery route optimization
- **Website Embed**: Get embed code for external websites (Wix, Jimdo, etc.)
MD
            ],
            [
                'slug' => 'legacy-import',
                'title' => 'Legacy Import (Access DB)',
                'icon' => 'database',
                'role' => 'santa',
                'content' => <<<'MD'
## Legacy Import

### What Are the Access Database Files?
The food drive previously used Microsoft Access databases. There are two types:
- **_be (Backend)**: Contains the actual data — this is the main database to import
- **_fe (Frontend/Admin)**: Contains forms and queries only, no importable data

### Importing
1. Go to **Seasons > Import > Access Tables**
2. Upload the **_be** file or use files already in `storage/app/imports/`
3. Click **Import All** to import all tables automatically
4. Or preview individual tables and import selectively

### Troubleshooting
- If 0 families are imported, ensure you're using the **_be** file
- The system maps legacy columns to the new schema automatically
- Delivery status values are normalized (e.g., "DELIVERED" → "delivered")
MD
            ],
        ];
    }
}
