<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        $faqs = self::faqs();
        return view('help.index', compact('faqs'));
    }

    public static function faqs(): array
    {
        return [
            ['q' => 'How do I register my family for the food drive?', 'a' => 'If online self-registration is open, visit the homepage and click "Register Your Family." Fill out the form with your family details and children\'s information. If registration is closed, contact your school\'s advisor or email the food drive team directly.'],
            ['q' => 'What is Adopt-a-Tag?', 'a' => 'Adopt-a-Tag lets community members "adopt" a child\'s gift tag and purchase a gift for them. You can browse available tags online (when the portal is open) or pick up a physical tag from the Giving Tree at Granite Falls High School.'],
            ['q' => 'How do I check my family\'s status?', 'a' => 'When your family is registered, you receive a unique status link (looks like: yoursite.com/family-status/abc123...). Use this link to check your registration status, delivery updates, and gift adoption progress.'],
            ['q' => 'Who are NINJAs?', 'a' => 'NINJAs are our amazing general volunteers! The name stands for "Neighbors In Need of Joining the Action." Everyone — NINJAs, coordinators, and staff — participates in shopping and delivery activities.'],
            ['q' => 'When is delivery day?', 'a' => 'Delivery dates are set by the Santa (administrator) in the system settings. Check the homepage or ask your coordinator for the specific dates. Typically deliveries happen in mid-December.'],
            ['q' => 'How does the warehouse barcode scanner work?', 'a' => 'The kiosk page has a barcode scanner input. Scan any food item barcode — the system looks it up in its local database first, then checks OpenFoodFacts for product info and auto-categorizes it. You can also type barcodes manually.'],
            ['q' => 'What are the different user roles?', 'a' => 'There are four roles: Family/Advisor (register families, manage their own entries), Self-Service (families who self-registered), Coordinator (manage families, warehouse, shopping, delivery operations), and Santa (full admin access including settings, user management, and system configuration).'],
            ['q' => 'How do gift tags work?', 'a' => 'Each child in a registered family gets a gift tag with their age, gender, sizes, and gift preferences. Tags can be printed (Coordinator > Gift Tags) and placed on the Giving Tree, or made available online through the Adopt-a-Tag portal.'],
            ['q' => 'Can I adopt more than one tag?', 'a' => 'Yes! You can adopt as many tags as you\'d like — individual children or even an entire family. Each adoption is tracked separately so you\'ll get reminders for each one.'],
            ['q' => 'Where do I drop off donations?', 'a' => 'Food, toiletries, and gifts should be dropped off at Granite Falls High School (GFHS). Check the homepage for the current address and any special drop-off instructions.'],
            ['q' => 'How does the shopping list work?', 'a' => 'The Santa sets up a grocery formula (e.g., 2 cans of soup per family member). The system automatically calculates quantities based on registered families. On shopping day, NINJAs and coordinators are assigned items to purchase.'],
            ['q' => 'What is the Command Center?', 'a' => 'The Command Center is a full-screen, auto-refreshing dashboard designed for display on TVs during operations. It has three modes: Overview (stats and charts), Shopping (NINJA progress tracking), and Delivery (live map with driver locations).'],
            ['q' => 'How do delivery routes work?', 'a' => 'Santa or coordinators can create optimized delivery routes using the Quick Assign feature, which groups nearby families and calculates efficient driving routes. Drivers get a mobile-friendly view with turn-by-turn navigation links.'],
            ['q' => 'What happens if I can\'t deliver a gift I adopted?', 'a' => 'Contact the food drive team as soon as possible. An administrator can release your tag back to the available pool so someone else can adopt it. Don\'t worry — the important thing is that every child gets a gift.'],
            ['q' => 'Is my family\'s information kept confidential?', 'a' => 'Yes. All family information is kept strictly confidential. Only authorized coordinators and administrators can see family details. Gift tag adopters only see the child\'s age, gender, and gift preferences — never names or addresses.'],
            ['q' => 'How do I volunteer?', 'a' => 'Contact the food drive team via the email on the homepage, or speak to a coordinator at your school. Volunteers help with sorting donations, wrapping gifts, shopping for groceries, and delivering on delivery day.'],
            ['q' => 'What does "severe need" mean?', 'a' => 'Some families are flagged as having severe or urgent needs by advisors or Santa. These families are silently prioritized in the Adopt-a-Tag portal to ensure they get adopted first, but no special indicator is shown to adopters.'],
            ['q' => 'Can I use the system on my phone?', 'a' => 'Yes! The entire system is mobile-responsive. The driver view, shopping companion, and Adopt-a-Tag portal are specifically optimized for mobile use. The warehouse kiosk works well on tablets too.'],
        ];
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

Welcome to the GFSD Food & Gift Drive management system. This application manages the entire lifecycle of the Granite Falls School District's annual food and gift drive — from family registration and gift tag creation through shopping, warehouse intake, and delivery day operations. This guide covers the basics you need to know to log in, navigate the system, and understand what your role allows you to do.

---

### Purpose

The GFSD Food & Gift Drive runs every December. Families in need register through the system (or are registered by coordinators). Each child in those families gets gift tags that community members can adopt. Volunteers (NINJAs) and coordinators shop for groceries together. Everything is packed into food boxes and delivered to families on delivery day. This app ties all of those moving pieces together in one place.

---

### Logging In

There are two ways to log into the system:

#### Username & Password Login
1. Visit the site and click **Staff Login** in the top navigation bar
2. Enter the **username** and **password** that were provided to you by a Santa (admin)
3. Click **Log In**

**Important:** This system uses usernames, not email addresses. Your username was set when your account was created. If you do not know your username, ask a Santa to look it up under **Santa > Users**.

#### Google OAuth Login
If Google OAuth has been configured by an admin:
1. Click **Sign in with Google** on the login page
2. Choose your Google account
3. If your Google account is already linked to a user account, you will be logged in immediately
4. If your Google account is not recognized, you will be taken to an **Access Request** page where you can submit a request. A Santa must approve it before you can log in

**Note:** Google OAuth must be enabled in **Settings > Integrations** and requires a Google Client ID and Client Secret to be configured. The admin can also restrict which email domains are allowed (e.g., only `@gfrhinoclub.org` emails).

---

### Understanding Roles

Every user account has a role that determines what they can see and do. There are three roles:

| Role | Permission Level | What You Can Do |
|------|-----------------|-----------------|
| **Family** | Basic (7) | View your assigned families, see children and gift details. Read-only access to most features. |
| **Coordinator** | Mid-level (8) | Everything Family can do, plus: manage families, add/edit children, print gift tags, generate family summaries, access the warehouse module, view delivery sheets. |
| **Santa** | Full Admin (9) | Everything Coordinator can do, plus: assign family numbers, manage users, configure settings, access the Command Center, manage seasons/imports, run reports, manage delivery routes and teams. |

Your role is assigned when your account is created. Only Santas can change roles.

---

### Your Dashboard

After logging in, you are automatically redirected to the dashboard appropriate for your role:

- **Family role** → Family list page showing the families assigned to your account
- **Coordinator role** → Coordinator dashboard with statistics (total families, children, gift coverage, unmerged tags) and quick-action cards for each school
- **Santa role** → Santa dashboard with admin tools, links to all management pages, and season overview

---

### Navigation

The **top navigation bar** is your primary way to move around the app. The links you see depend on your role:

- **Family**: Family list
- **Coordinator**: Family list, Coordinator tools (Gift Tags, Family Summary), Warehouse, Delivery Day
- **Santa**: All of the above, plus Santa admin (Users, Settings, Seasons, Number Assignment, Shopping List, Command Center, Delivery Routes, Reports)

On **mobile devices**, the navigation collapses into a hamburger menu (three horizontal lines) in the top-left corner. Tap it to expand the full navigation.

The **Help** link (this wiki) is available to all logged-in users from the navigation bar.

---

### Step-by-Step: Your First Login

1. Open the site URL in your browser (Chrome or Edge recommended)
2. Click **Staff Login**
3. Enter your username and password
4. You will land on your role-appropriate dashboard
5. Explore the navigation bar to see what sections are available to you
6. If something looks wrong or you think you should have more access, contact a Santa

---

### Tips & Best Practices

- **Bookmark the site** so you can quickly access it during the busy drive season
- **Use Chrome or Edge** for the best experience — some features (like barcode scanning and PDF generation) work best in Chromium-based browsers
- **On delivery day**, drivers should use their phone browser for the Driver View — it is mobile-optimized
- **Keep your password private.** If you need a password reset, ask a Santa — they can reset it from the Users page

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| "Invalid credentials" on login | Double-check your **username** (not email). Usernames are case-sensitive. Ask a Santa to reset your password if needed. |
| Google login shows "Access Denied" | Your Google account is not linked to a user account yet. Submit an access request and wait for a Santa to approve it. |
| Cannot see certain menu items | Your role may not have permission. Ask a Santa to verify your role if you believe it is incorrect. |
| Page looks broken on mobile | Make sure you are using a modern browser. Try clearing your cache or using Chrome. |
| Session expired / logged out unexpectedly | Sessions expire after a period of inactivity. Simply log in again. |

---

### FAQ

**Q: Can I change my own password?**
A: No. Only Santas can reset passwords. Contact a Santa if you need yours changed.

**Q: Can I have multiple roles?**
A: No. Each account has exactly one role. If you need elevated access temporarily, a Santa can change your role and change it back later.

**Q: Is there a mobile app?**
A: No, but the website is fully responsive and works well on phones and tablets. The Driver View and Shopping Companion pages are specifically designed for mobile use.
MD
            ],
            [
                'slug' => 'family-management',
                'title' => 'Managing Families',
                'icon' => 'users',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Managing Families

The family management system is the core of the food drive. Every family that will receive food boxes and gifts needs to be in this system with accurate information. This section covers how families get into the system, how to edit their information, how to add children, and how the self-registration process works.

---

### Purpose

Families are the central data object in the application. A family record contains the household information (name, address, phone, number of members) and links to child records (which contain gift tag details like age, gender, clothing sizes, and toy preferences). Accurate family data drives everything downstream: gift tag generation, shopping list quantities, delivery routing, and warehouse needs calculations.

---

### Adding a Family Manually

Coordinators and Santas can add families directly:

1. Navigate to **Family > Add Family** from the top navigation
2. Fill in the required fields:
   - **Family Name** — the household surname (e.g., "Smith" or "Johnson Family")
   - **Address** — full street address including city, state, and ZIP code
   - **Phone 1** — primary phone number
   - **Phone 2** (optional) — secondary/alternate phone
3. Fill in household details:
   - **Number of Adults** — how many adults live in the household
   - **Number of Family Members** — total household size (adults + children). This is used for grocery shopping list calculations
   - **Delivery Preference** — choose **Delivery** (we bring it to them) or **Pickup** (they come to us)
4. Optionally fill in:
   - **Notes** — any special instructions, dietary restrictions, access codes for gated communities, etc.
   - **Delivery Date** — which delivery date this family is assigned to (if multiple delivery days are configured)
   - **School** — the school the family is associated with (used for family number assignment by school range)
5. Click **Save**

After saving, you will be taken to the family detail page where you can add children.

---

### Family Numbers

Family numbers are a critical identifier used throughout the drive. They appear on gift tags, delivery sheets, food boxes, and shopping lists. Numbers are organized by school ranges (e.g., Mountain Way Elementary gets numbers 1-50, Granite Falls High gets 51-100).

**How family numbers are assigned:**
- Only **Santas** can assign family numbers (via **Santa > Number Assignment**)
- Numbers can be assigned manually by typing a number next to each family
- Or use **Auto-Assign** to automatically assign numbers based on school ranges
- The system groups unassigned families by their oldest child's school and assigns the next available number in that school's range

**Important:** A family without a number will not appear on gift tags, delivery sheets, or shopping lists. Make sure all families get numbers before printing tags.

---

### Editing a Family

1. Navigate to the family list and click on a family name to open their detail page
2. Click **Edit** (or the pencil icon)
3. Update any fields as needed
4. Click **Save**

Changes take effect immediately. If gift tags have already been printed for this family, you may need to reprint them to reflect updated information.

---

### Adding Children to a Family

Children are added from the family detail page. Each child record generates a gift tag.

1. Open a family's detail page
2. Scroll down to the **Children** section
3. Click **Add Child**
4. Fill in the child's information:

| Field | Description | Used On |
|-------|-------------|---------|
| **First Name** | Child's first name | Gift tag, internal reference |
| **Age** | Child's age in years | Gift tag (helps adopters choose age-appropriate gifts) |
| **Gender** | Boy / Girl | Gift tag (determines gift suggestions) |
| **School** | Which school the child attends | Family number grouping |
| **Shirt Size** | e.g., Youth M, Adult S | Gift tag |
| **Pant Size** | e.g., 10, 12, Adult 30x30 | Gift tag |
| **Shoe Size** | e.g., Youth 3, Adult 8 | Gift tag |
| **Toy Ideas** | What the child would like | Gift tag (guides adopters) |
| **Gift Preferences** | Additional gift notes | Gift tag |

5. Click **Save**
6. Repeat for each child in the family

**Editing a child:** Click the pencil icon next to any child on the family detail page.

**Deleting a child:** Click the trash icon next to the child. This is permanent and cannot be undone. If a gift tag was already printed for this child, it becomes invalid.

---

### The "Done" Toggle

Each family has a **Done** toggle (checkmark button) on the family list. This is a simple way to track whether a family's information has been reviewed and finalized. When toggled:
- The family row gets a visual indicator (green checkmark)
- This is purely organizational — it does not affect gift tags, delivery, or anything else

Use this to track your progress when working through the family list (e.g., "I've verified all info for this family and added all their children").

---

### Self-Registration

When enabled by a Santa in **Settings > Public Features > Self-Registration**, families can register themselves through a public URL.

**How it works:**
1. The family visits the public registration URL (linked from the homepage when enabled)
2. They fill in their household information: name, address, phone, number of adults, number of children
3. They can add each child's details: name, age, gender, clothing sizes, toy preferences
4. They submit the form
5. Their submission appears in the family list with all the information they provided
6. A coordinator or Santa reviews the submission and makes any corrections

**Self-Registration Fields:**
- Family name, address, phone numbers
- Number of adults and total family members
- Delivery preference (delivery or pickup)
- Children details (name, age, gender, sizes, preferences)
- Notes / special instructions

**When self-registration is closed**, visitors see a "Registration Closed" page with contact information for school district advisors so they can still get help.

---

### Family Status Pages

When enabled in settings, each family gets a unique status page URL. This URL can be shared with the family so they can check the status of their delivery without needing to log in. The status page shows:
- Their delivery status (Pending, In Transit, Delivered, Picked Up)
- Their assigned delivery date
- Basic gift coverage status

A coordinator can regenerate the status token if the URL needs to be invalidated and reissued.

---

### How Families Connect to Other Features

- **Gift Tags** — each child in a family generates a gift tag. No family number = no tag.
- **Shopping Lists** — the total number of family members drives grocery quantity calculations.
- **Delivery Routing** — the family's address is geocoded (latitude/longitude) for route optimization and the live map.
- **Warehouse** — the deficit table calculates how many food boxes and gifts are needed based on the total number of families and children.
- **Adopt-a-Tag** — children's gift tags can be browsed and claimed by community members online.

---

### Tips & Best Practices

- **Verify addresses early.** Bad addresses cause geocoding failures which break delivery routing. If an address cannot be geocoded, the family will not appear on the delivery map.
- **Fill in all child sizes.** Adopters rely on the clothing sizes on the gift tag. Missing sizes mean the child may get the wrong size.
- **Use the Notes field** for anything unusual: gated community codes, aggressive dogs, "leave at side door," dietary restrictions, etc.
- **Assign family numbers by school** to keep families from the same school together. This makes delivery routing more efficient since families near the same school tend to live in the same area.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| Family does not appear on gift tags | The family has no family number assigned. Ask a Santa to assign one via Number Assignment. |
| Family does not appear on the delivery map | The address could not be geocoded. Check that the address is complete and correctly formatted. A Santa can run batch geocoding from the admin panel. |
| Duplicate family entries | Use **Santa > Duplicates** to detect and merge duplicate families. The system uses fuzzy matching on name and address. |
| Self-registration form not showing | Self-Registration must be enabled in **Settings > Public Features**. Check that the toggle is on. |
| Family information changed after tags were printed | Reprint the gift tags. Use the family number range filter to print only the affected family's tags. |

---

### FAQ

**Q: Can a family have zero children?**
A: Yes, but they will not have any gift tags. They will still receive a food box based on the number of family members.

**Q: What happens if two families have the same family number?**
A: The system prevents this. Family numbers must be unique. If you try to assign a number that is already taken, you will get an error.

**Q: Can I delete a family?**
A: Currently, families are not deleted — they remain in the system for the season. If a family drops out, you can remove their family number so they no longer appear on tags or delivery sheets.
MD
            ],
            [
                'slug' => 'gift-tags',
                'title' => 'Gift Tags & Printing',
                'icon' => 'tag',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Gift Tags & Printing

Gift tags are the physical (or digital) cards that go on the Giving Tree or are posted online for community members to adopt. Each tag represents one child and contains the information an adopter needs to buy appropriate gifts. This section covers how to generate, print, and manage gift tags, as well as the Adopt-a-Tag online portal.

---

### Purpose

The gift tag system serves two purposes:
1. **Physical tags** are printed and hung on Giving Trees at schools, businesses, and community locations. Community members take a tag, buy the gifts described on it, and return them.
2. **Digital tags** (via Adopt-a-Tag) let community members browse and claim tags online, expanding reach beyond physical tree locations.

---

### Printing Gift Tags — Step by Step

1. Navigate to **Coordinator > Gift Tags** from the top navigation
2. Choose your **filter**:
   - **Unmerged** (default) — only children whose tags have not been printed yet. This is the most common choice to avoid reprinting.
   - **All** — every child with a family number, regardless of print status
   - **Family Number Range** — enter a start and end number to print tags for a specific range (useful for printing one school at a time)
3. Optionally check **Mark as merged** — this flags all included children as "printed" so they will not appear in the Unmerged filter next time
4. Click **Generate**
5. The tags will open as a **PDF** (if DomPDF is installed) or as an **HTML page** (which you can print from the browser)

**Adopted children are automatically excluded** from gift tag printing. If a child's tag has been claimed through the Adopt-a-Tag portal, their tag will not appear in the print output since the adopter is already handling their gifts.

---

### Tag Layout Details

Tags are laid out at **10 per page** (5 rows x 2 columns) on US Letter or A4 paper (configurable in Settings).

Each tag contains:
- **Family Number** — large and prominent, used to match returned gifts to the correct family
- **Child's Gender** — Boy or Girl
- **Child's Age** — in years
- **Shirt Size** — e.g., Youth M, Adult S
- **Pant Size** — e.g., 10, 12, 28x30
- **Shoe Size** — e.g., Youth 3, Adult 8
- **Toy Ideas** — what the child wants (from the child record)
- **Gift Preferences** — additional notes about what the child likes
- **QR Code** — links to either the Adopt-a-Tag page (when enabled) or a scan URL for warehouse intake
- **Adopt-a-Tag Deadline** — shown in the footer when a deadline is configured (e.g., "Please return gifts by December 10th")

---

### Understanding the Merge/Print Tracking System

The system tracks which tags have been "merged" (printed). This is important because you will typically print tags in batches — first round for the Giving Tree, then a second round for late registrations.

- **Unmerged** = the tag has never been printed
- **Merged** = the tag has been printed at least once

When you check **Mark as merged** during generation, all tags in that batch are flagged as merged. Next time you generate with the **Unmerged** filter, only new children (added since the last print) will appear.

**To reprint tags** that were already merged, switch the filter to **All** or use a family number range.

---

### Adopt-a-Tag Portal

The Adopt-a-Tag portal is a public-facing website where community members can browse available gift tags online and claim them digitally.

**Setup:**
1. Go to **Settings > Public Features > Adopt-a-Tag**
2. Toggle **Enable Adopt-a-Tag**
3. Set the **Deadline** — the date by which adopters must return their gifts (e.g., "December 10, 2025")
4. Optionally add a **Custom Message** that appears on the portal (e.g., "Thank you for supporting our community!")

**How it works for adopters:**
1. They visit the `/adopt` URL (linked from the homepage)
2. They see a grid of available tags showing gender, age, and sizes (family names are never shown to protect privacy)
3. They click on a tag to see full details
4. They enter their name, email, and phone to **Claim** the tag
5. They receive a confirmation page with their claimed tag details and a unique link to track their adoption
6. When they return the gift, they can mark it as delivered through their confirmation link, or the warehouse team logs it via Gift Drop-Off

**Admin management:**
- Santas can view all adoptions at **Santa > Adoptions**
- They can **release** a claim (if an adopter backs out) so the tag becomes available again
- They can **complete** an adoption (mark the gift as received)
- Claimed tags are automatically excluded from physical gift tag printing

---

### QR Codes on Tags

Every gift tag includes a QR code. What the QR code links to depends on the Adopt-a-Tag setting:

- **Adopt-a-Tag enabled** → QR code links to that child's page on the Adopt-a-Tag portal, making it easy for someone at the Giving Tree to claim the tag digitally right from their phone
- **Adopt-a-Tag disabled** → QR code links to a signed scan URL used by warehouse staff during gift drop-off

---

### How Gift Tags Connect to Other Features

- **Family Management** — child data (sizes, preferences) flows directly onto the tags
- **Adopt-a-Tag Portal** — digital version of the tags for online claiming
- **Warehouse Gift Drop-Off** — when a gift comes in, the QR code identifies which child it belongs to
- **Command Center** — the gift coverage statistics (None / Partial / Moderate / Full) are tracked per child and displayed on the Command Center overview

---

### Tips & Best Practices

- **Print tags in batches by school.** Use the family number range filter to print one school at a time. This makes it easier to distribute tags to the correct Giving Tree location.
- **Always use "Mark as merged"** when doing your main print run. This prevents accidentally reprinting the same tags.
- **Print on cardstock** if possible. Tags printed on regular paper are flimsy and can get damaged on the tree.
- **Set the Adopt-a-Tag deadline** at least 9 days before the first delivery date. This gives the warehouse team time to process returned gifts.
- **Do a test print** of 2-3 tags before printing the full batch to verify formatting and paper alignment.
- **Check for missing sizes** before printing. Run through the children list and fill in any blank size fields — adopters need this information.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| No tags appear when generating | Make sure families have been assigned family numbers. Children without a family number are excluded. Also check the filter — if set to "Unmerged" and all tags are already merged, nothing will show. |
| Tags are blank or missing information | Check the child records. If age, gender, or sizes are empty, those fields will be blank on the tag. |
| PDF does not generate (shows HTML instead) | The DomPDF package is not installed. You can still print from the HTML page using your browser's print function (Ctrl+P). |
| QR code does not scan | Make sure the QR code is large enough and not smudged. The tag layout sizes QR codes at approximately 1 inch square. |
| Tags for adopted children are appearing | Adopted children should be automatically excluded. If they are appearing, check whether the adoption was properly recorded in the system. |
| Wrong paper size | Change the paper size in **Settings** (Letter vs. A4) before generating tags. |

---

### FAQ

**Q: Can I print a single child's tag?**
A: Yes. Use the family number range filter and set both start and end to that family's number. Only that family's children's tags will appear.

**Q: What happens if I forget to check "Mark as merged"?**
A: Nothing bad happens — the tags are still generated correctly. But next time you use the "Unmerged" filter, those same tags will appear again. You can go to the Gifts page and manually mark children as merged if needed.

**Q: How many tags fit on one page?**
A: 10 tags per page — 5 rows of 2 tags each.
MD
            ],
            [
                'slug' => 'delivery-day',
                'title' => 'Delivery Day',
                'icon' => 'truck',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Delivery Day

Delivery day is the culmination of the entire food drive — the day when food boxes and gifts are loaded into vehicles and delivered to families across the community. The system provides a full suite of tools for managing routes, dispatching teams, tracking progress in real time, and coordinating volunteers. This section covers the Dispatch Board, Route Builder, Teams, Live Map, Driver View, and location sharing.

---

### Purpose

On delivery day, dozens of volunteers are moving simultaneously across town. Without a system, it is chaos — nobody knows which families have been delivered to, which are still pending, or where the drivers are. The Delivery Day module solves this by providing:
- A central **Dispatch Board** where coordinators see every family's status at a glance
- A **Route Builder** to create optimized delivery routes
- A **Live Map** showing driver locations and delivery progress in real time
- A **Driver View** that gives each driver a mobile-friendly route on their phone
- An **Activity Feed** so everyone can see what is happening as it happens

---

### Preparation Before Delivery Day

Before delivery day, several things need to be set up:

1. **All families must have addresses geocoded.** Go to **Santa > Geocode Families** to batch-geocode all family addresses. Families without coordinates will not appear on the map or be available for route optimization.
2. **Create Delivery Teams.** Go to the **Teams** tab on the Delivery Day page and create teams with names, colors, and optionally assigned drivers. Teams are color-coded on the map. Examples: "Red Team - North Route", "Blue Team - South Route".
3. **Build Routes.** Use the **Route Builder** tab to create routes. Each route is a named sequence of family stops. You can drag families in from the unrouted pool or use the **Optimize** button (requires OpenRouteService API key) to automatically find the most efficient order.
4. **Set Delivery Dates.** In **Settings > Operations > Delivery Dates**, set the delivery date(s). If you have multiple delivery days (e.g., December 18th and 19th), families can be assigned to specific dates.
5. **Set Delivery Time Range.** In **Settings > Operations**, configure the delivery time window (e.g., 8:00 AM to 9:00 PM). This appears on delivery sheets and the Driver View.

---

### The Dispatch Board

The Dispatch Board is the main operational view during delivery day. It shows every family with a family number, grouped by team and sorted by family number.

**For each family, you can see:**
- Family number and name
- Address and phone number
- Delivery preference (Delivery or Pickup)
- Current delivery status (color-coded badge)
- Assigned team (color-coded)
- Assigned route
- Number of children
- Notes
- Recent delivery log entries

**Changing delivery status:**
Use the dropdown next to each family to change their status. Changes are saved via AJAX — no page reload needed.

| Status | Meaning | Color |
|--------|---------|-------|
| **Pending** | Not yet started | Gray |
| **In Transit** | Team is on the way or has attempted delivery | Yellow |
| **Delivered** | Successfully delivered | Green |
| **Picked Up** | Family came and picked up their items | Blue |

**Filtering:**
- Filter by **Team** to see only one team's families
- Filter by **Status** to see only pending, in transit, delivered, etc.
- The **"Needs Delivery"** filter shows families who requested delivery and have not been delivered to yet

**Adding delivery logs:**
Click the log icon next to a family to add a delivery log entry. Log statuses include:
- **Delivered** — successful delivery
- **Left at Door** — nobody answered, items left at door
- **No Answer** — nobody home, will retry
- **Attempted** — tried but could not deliver (e.g., address not found)
- **Picked Up** — family came to pickup location
- **Note** — general note without status change

Logs automatically update the family's delivery status for terminal statuses (delivered, picked up) and set it to "in transit" for attempted/left at door.

---

### Route Builder

The Route Builder lets you create and manage delivery routes.

**Creating a route:**
1. Go to the **Route Builder** tab
2. Click **New Route**
3. Give the route a name (e.g., "Route 1 - Mountain Way Area")
4. Optionally assign a driver from the dropdown
5. Save the route

**Adding families to a route:**
- The left side shows **unrouted families** (families with coordinates that are not assigned to any route)
- Drag families into a route, or select them and use the assign button
- Families are ordered within a route by their route_order field

**Optimizing a route:**
1. Select a route
2. Click **Optimize** (requires an OpenRouteService API key configured in Settings)
3. The system sends all the stop coordinates to the OpenRouteService API and returns the optimal driving order
4. Family stop order is automatically updated

**Route tokens:**
Each route gets a unique token URL. This is the link you give to the driver — they open it on their phone and get the mobile Driver View for just their route.

---

### Teams

Teams are organizational groups for delivery day. Each team has:

| Field | Description |
|-------|-------------|
| **Name** | Team name (e.g., "Red Team", "North Route") |
| **Color** | Hex color code used on the map and dispatch board |
| **Driver** | Optional — assign a user account as the team's driver |

Teams are created on the **Teams** tab. You can bulk-assign families to teams from the Dispatch Board.

---

### Live Map

The Live Map is a full-screen interactive map showing all families, routes, and driver locations.

**How to access it:** Click **Live Map** from the Delivery Day page.

**What the map shows:**
- **Family markers** — color-coded by delivery status (gray=pending, yellow=in transit, green=delivered, blue=picked up)
- **Route polylines** — colored lines connecting the stops in each route, color-matched to the team
- **Driver locations** — blue pulsing dots showing where each volunteer/driver currently is (when they are sharing their location)
- **Route start/end points** — the warehouse or starting location for each route

**Interacting with the map:**
- Click any family marker to see a popup with family details (name, address, phone, status)
- From the popup, you can change the delivery status directly
- Filter by team or route using the controls
- The map auto-refreshes data periodically

---

### Driver View

The Driver View is a mobile-optimized page designed for drivers to use on their phones during delivery.

**How to access it:**
1. Each route has a unique token URL
2. Copy the URL and send it to the driver (via text message, email, etc.)
3. The driver opens it on their phone — no login required

**What the driver sees:**
- Route name and progress bar showing how many stops are completed
- List of stops in order, each showing:
  - Family number and name
  - Address (tappable — opens in Google Maps or Apple Maps for turn-by-turn navigation)
  - Phone number (tappable — opens phone dialer)
  - Delivery status
  - Number of children
  - Notes
- A big **"Mark Delivered"** button for each stop
- Completed stops move to the bottom with a green checkmark

**Auto-refresh:** The Driver View refreshes data every 15 seconds so the driver always sees the latest status (e.g., if dispatch marks a family as "picked up" from the board, it updates on the driver's phone).

**Location sharing from Driver View:**
The Driver View has a **Share Location** button. When tapped:
- The driver's phone shares its GPS coordinates with the server
- The driver's position appears as a dot on the Live Map and Command Center
- Location updates are sent periodically while sharing is active
- Locations are considered stale after 10 minutes of no updates

---

### Delivery Logs & Activity Feed

Every status change and note is recorded in the delivery log. You can view the full log at **Delivery Day > Logs** with filters for:
- Date
- Family

The 8 most recent log entries also appear in the **Command Center** delivery mode as a live activity feed.

---

### How Delivery Day Connects to Other Features

- **Family Management** — family addresses and delivery preferences drive routing
- **Command Center** — delivery mode shows the live map, route progress, and activity feed on a big screen
- **Warehouse** — families must have their food boxes packed and gifts collected before delivery
- **Settings** — delivery dates, time ranges, and return-to information appear on delivery sheets

---

### Tips & Best Practices

- **Geocode all families the day before** delivery day. This ensures every family appears on the map and in the route optimizer.
- **Create routes by geographic area** — families that are near each other should be on the same route to minimize driving time.
- **Print delivery sheets** as a backup. Technology can fail on delivery day. Have paper copies of each route with addresses and phone numbers.
- **Assign a dedicated dispatcher** — one person (coordinator or santa) should sit at the Dispatch Board and be the single point of contact for driver questions.
- **Test the Driver View** before delivery day. Send the link to yourself and make sure it loads, shows the route, and allows status updates.
- **Charge phones.** Location sharing and GPS navigation drain batteries quickly. Drivers should bring car chargers.
- **Use the "Left at Door" log status** when nobody answers. This creates a record and keeps the family in "in transit" status so the team can retry later.
- **Set the delivery return-to information** in Settings so delivery sheets show where drivers should return after completing their route.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| Family does not appear on the map | The family's address was not geocoded. Run batch geocoding from Santa > Geocode Families. |
| Route optimization fails | Check that the OpenRouteService API key is configured in Settings > Integrations. Also verify that all families in the route have coordinates. |
| Driver View shows "Route not found" | The route token may have changed. Regenerate the Driver View link and send the new URL to the driver. |
| Driver location not showing on map | The driver needs to tap "Share Location" on the Driver View and allow browser location permissions. Their position goes stale after 10 minutes if sharing stops. |
| AJAX status update fails | Check internet connectivity. The page will show an error if the update could not be saved. Refresh the page and try again. |
| Delivery status changed by accident | Add a delivery log note explaining the correction, then change the status to the correct value. All changes are logged. |

---

### FAQ

**Q: Can the Driver View work without internet?**
A: No. The Driver View requires an internet connection to load and to save status updates. However, the driver can use offline-capable mapping apps (Google Maps, Apple Maps) for navigation by tapping the address links.

**Q: Can multiple people update the same family's status at the same time?**
A: Yes, but the last update wins. The system does not lock records. In practice, only the assigned driver or the dispatcher should be updating statuses.

**Q: What if a family is not home?**
A: Use the "No Answer" or "Left at Door" log entry. The family stays in "In Transit" status. The team can retry later, or the dispatcher can call the family to arrange a pickup.
MD
            ],
            [
                'slug' => 'shopping',
                'title' => 'Shopping Lists',
                'icon' => 'cart',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Shopping Lists

The Shopping List module manages the grocery items that go into each family's food box. It uses a formula system to calculate exactly how much of each item is needed based on the number of families and their household sizes. On Shopping Day, assignments are created for all shoppers — both NINJAs (volunteers) and coordinators — so they can check off items in real time from their phones.

---

### Purpose

Every family receives a food box containing groceries. The quantity of each item depends on the family's size — a family of 2 needs less than a family of 8. The shopping list formula calculates totals automatically so you know exactly how much to buy. On the day of the shopping trip, everyone — NINJAs and coordinators alike — gets mobile-friendly checklists so nothing gets missed.

---

### The Grocery Formula

The grocery formula is managed at **Santa > Shopping List**. This is where you define what goes into the food boxes and how quantities scale.

**Adding a grocery item:**
1. Go to **Santa > Shopping List**
2. Click **Add Item**
3. Fill in the fields:

| Field | Description | Example |
|-------|-------------|---------|
| **Name** | Item name | "Canned Green Beans" |
| **Description** | Additional detail or brand preference | "14.5 oz cans, store brand OK" |
| **Base Quantity** | Number of this item every family gets, regardless of size | 2 |
| **Per-Person Multiplier** | Additional quantity per family member | 0.5 |
| **Category** | Grouping for display (optional) | "Canned Vegetables" |

4. Click **Save**

**How the formula works:**
For each family, the total quantity of an item is:
> **Total = Base Quantity + (Per-Person Multiplier x Number of Family Members)**

**Example:** Canned corn with base=1, per-person=0.5:
- Family of 2: 1 + (0.5 x 2) = **2 cans**
- Family of 4: 1 + (0.5 x 4) = **3 cans**
- Family of 8: 1 + (0.5 x 8) = **5 cans**

The system calculates the grand total across all families so you know exactly how many cans to buy.

**Importing and exporting formulas:**
- **Import**: Upload a spreadsheet of grocery items to bulk-add them. Useful when reusing last year's formula.
- **Export**: Download the current formula as a spreadsheet for reference or to share with the shopping team.

---

### Shopping Day Assignments

On Shopping Day, you create assignments that pair shoppers (NINJAs and coordinators) with specific items or sections of the shopping list.

**Creating an assignment:**
1. Go to **Santa > Shopping Day**
2. Click **Create Assignment**
3. Select a **shopper** — either a coordinator or a NINJA (volunteer), or enter a name for a non-registered volunteer
4. Define what they are shopping for (items, categories, or family ranges)
5. Save the assignment

Each assignment generates a **unique token URL**. This URL is the NINJA's mobile shopping companion.

**The NINJA Shopping Companion:**
When a NINJA opens their assignment URL on their phone, they see:
- A list of items they need to buy with quantities
- Checkboxes next to each item
- As they check off items, their progress is saved in real time
- The NINJA does not need to log in — the URL is their access token

**Sharing the link:**
Copy the assignment URL and send it to the NINJA via text message. They open it on their phone and start shopping.

---

### NINJA Progress Tracking

As NINJAs check off items, their progress is visible in two places:

1. **Shopping Day page** — shows each NINJA's assignment with a progress bar
2. **Command Center (Shopping Mode)** — shows all NINJA progress bars on the big screen so the operations room can see who is done and who needs help

This real-time visibility means the Shopping Day coordinator can quickly identify if a NINJA is stuck or falling behind and send help.

---

### How Shopping Lists Connect to Other Features

- **Family Management** — the number of family members drives the per-person multiplier calculation
- **Command Center** — Shopping Mode displays NINJA progress in real time
- **Warehouse** — after shopping, purchased items are brought to the warehouse for intake and packing

---

### Tips & Best Practices

- **Set up the grocery formula well before Shopping Day.** Review it with the Food Manager to make sure nothing is missing and quantities are right.
- **Create a test assignment** and try the mobile companion yourself before sending links to NINJAs. Make sure it loads and checkboxes work.
- **Group items logically** in assignments. For example, give one NINJA all the canned goods, another all the fresh produce. This keeps shopping efficient.
- **Use the per-person multiplier of 0** for items that are the same regardless of family size (e.g., one turkey per family).
- **Over-buy slightly** — it is better to have a small surplus than to be short. The formula gives you exact numbers, but always round up.
- **Export the formula** before Shopping Day and print paper copies as a backup in case phones die.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| Shopping totals seem too low | Check that all families have the correct **Number of Family Members** filled in. A family with 0 members will only get the base quantity. |
| NINJA cannot open assignment link | Verify the link is correct and has not been modified. The URL must include the full token. Try copying and pasting it directly. |
| Items are not saving when checked off | The NINJA needs an internet connection. Checkmarks are saved via AJAX. If offline, checkmarks will not persist. |
| Formula imported with wrong quantities | Delete the imported items and re-import. Or edit each item individually on the Shopping List page. |

---

### FAQ

**Q: Can I reuse last year's grocery formula?**
A: Yes. Export the formula from last year, then import the spreadsheet into the new season. Adjust quantities as needed.

**Q: Do NINJAs need user accounts?**
A: Not necessarily. Assignments can be created with just a name for non-registered volunteers. They access their checklist via the token URL without logging in.

**Q: What if a store is out of an item?**
A: The NINJA should leave it unchecked and notify the Shopping Day coordinator. The coordinator can reassign it or find a substitute.
MD
            ],
            [
                'slug' => 'command-center',
                'title' => 'Command Center',
                'icon' => 'monitor',
                'role' => 'santa',
                'content' => <<<'MD'
## Command Center

The Command Center is a full-screen, auto-refreshing dashboard designed to be displayed on a large TV or monitor in the operations room. It provides a bird's-eye view of the entire food drive operation in real time. During Shopping Day, it shows NINJA progress. During Delivery Day, it shows the live map, route progress, and activity feed. It is the single most important visual tool during active operations.

---

### Purpose

On busy operational days (Shopping Day and Delivery Day), there are many people working simultaneously — NINJAs shopping at stores, drivers delivering across town, coordinators packing food boxes. The Command Center puts all of this activity on one screen so the operations team can:
- See overall progress at a glance
- Identify problems quickly (a NINJA stuck at 0%, a route with no deliveries)
- Celebrate milestones (all routes complete!)
- Keep everyone motivated with real-time numbers

---

### Accessing the Command Center

1. Navigate to **Santa > Command Center**
2. The page opens in a full-screen layout (no navigation bar, no sidebar — just the dashboard)
3. To exit, click the **X** button in the top corner or press Escape

**Direct URL:** You can also bookmark the Command Center URL and open it directly on the TV's browser.

---

### Dashboard Modes

The Command Center has three modes, selectable from the top bar:

#### Overview Mode
Displays high-level statistics for the entire drive:
- **Total Families** — how many families are registered with family numbers
- **Total Children** — how many children across all families
- **Total Members** — sum of all family members (used for food box calculations)
- **Gift Coverage** — breakdown by gift level (None / Partial / Moderate / Full) with percentages
- **Adopted Tags** — how many children have been adopted through the Adopt-a-Tag portal

This mode is useful **before** Shopping Day and Delivery Day to give a snapshot of where things stand.

#### Shopping Mode
Displays real-time NINJA shopping progress:
- **Overall progress bar** — total items checked off vs. total items across all assignments
- **Per-NINJA progress bars** — each NINJA's name, assignment description, and a progress bar showing checked/total items with a percentage
- Progress bars update automatically as NINJAs check off items on their phones

**Color coding:**
- 0-25% — Red
- 25-75% — Yellow
- 75-100% — Green

This mode should be active during **Shopping Day** so the operations room can monitor progress.

#### Delivery Mode
Displays real-time delivery operations:
- **Delivery progress bar** — total delivered+picked up vs. total families
- **Status breakdown** — counts for Pending, In Transit, Delivered, Picked Up
- **Route progress** — each route's name, driver, and completion percentage
- **Live map** — embedded map showing family markers, route lines, and driver locations
- **Activity feed** — the 8 most recent delivery log entries (e.g., "John delivered #42 Smith Family 2 minutes ago")
- **Driver locations** — blue dots on the map for volunteers sharing their location (positions shown if updated within the last 15 minutes)

This mode should be active during **Delivery Day**.

#### Auto Mode
When set to **Auto**, the Command Center automatically determines which mode to display based on current activity. If there are active shopping assignments with incomplete items, it shows Shopping Mode. If there are deliveries in progress, it shows Delivery Mode. Otherwise, it shows Overview Mode.

---

### Data Refresh

The Command Center fetches fresh data from the server **every 15 seconds** automatically. You do not need to refresh the page. The timestamp in the corner shows when the last data refresh occurred.

The data endpoint (`/santa/command-center/data`) returns:
- Overview statistics
- Shopping progress for all NINJAs
- Delivery status counts and route progress
- Gift coverage stats
- Recent activity log entries
- Driver GPS locations

---

### How the Command Center Connects to Other Features

- **Shopping Lists** — Shopping Mode pulls progress from NINJA shopping assignments
- **Delivery Day** — Delivery Mode pulls family statuses, route data, and driver locations
- **Family Management** — Overview Mode pulls family/children counts
- **Gift Tags / Adopt-a-Tag** — Overview Mode shows gift coverage and adoption stats
- **Driver View** — drivers sharing their location from the Driver View appear as dots on the Command Center map

---

### Setting Up the Command Center TV

**Recommended setup:**
1. Connect a laptop, Chromecast, Fire TV Stick, or smart TV to a large screen in the operations room
2. Open a web browser (Chrome recommended)
3. Log in with a **Santa** account
4. Navigate to the Command Center
5. Set the browser to full screen (F11 on most browsers)
6. Set the mode to **Auto** or manually select the appropriate mode

**Hardware tips:**
- A 55" or larger TV works best so numbers are readable from across the room
- Use a wired internet connection if possible for reliability
- Disable the screen's auto-sleep/screensaver so the display stays on all day
- If using a Chromecast, cast the Chrome tab directly

---

### Tips & Best Practices

- **Keep it visible** — mount the TV where everyone in the operations room can see it. It is a morale booster when people see deliveries ticking up.
- **Switch modes manually** if Auto mode is not choosing the right one. During the transition between shopping and delivery, there might be overlap.
- **Use Overview Mode** at the kickoff meeting to show everyone the scope of the drive — "Here's how many families we're serving today."
- **Leave it running all day.** The auto-refresh handles everything. There is no reason to touch it once set up.
- **Pair with a second screen** if possible — one for Command Center, one for the Dispatch Board that the dispatcher actively uses.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| Dashboard shows zeros or is blank | Verify you are logged in as a Santa. The Command Center requires Santa-level access. Also check that families have family numbers assigned. |
| Data is not refreshing | Check the timestamp in the corner. If it stopped updating, the browser may have lost connection. Refresh the page. |
| Map is not showing in delivery mode | The map requires an internet connection to load map tiles. Verify connectivity. Also check that families have been geocoded. |
| Driver dots not appearing on map | Drivers must actively share their location from the Driver View. Locations go stale after 15 minutes with no update. |
| Shopping bars not updating | NINJAs must have an internet connection for their checkmarks to sync. If a NINJA's bar is stuck, they may be offline. |
| Page looks cramped on small screen | The Command Center is designed for large screens (40"+). On smaller screens, some elements may overlap. Use a larger display. |

---

### FAQ

**Q: Can the Command Center run on a tablet?**
A: Yes, it works on tablets, but the experience is best on a large TV or monitor. The layout is designed for a 16:9 widescreen.

**Q: Does the Command Center work if I close my laptop lid (casting to TV)?**
A: It depends on your laptop settings. Most laptops sleep when the lid is closed, which stops the browser. Configure your laptop to "Do nothing" when the lid is closed (Power Settings) or use a dedicated device.

**Q: Can non-Santa users access the Command Center?**
A: No. The Command Center is restricted to Santa-level accounts. If a coordinator needs to see it, either display it on a shared TV or temporarily elevate their role.
MD
            ],
            [
                'slug' => 'settings',
                'title' => 'Admin Settings',
                'icon' => 'cog',
                'role' => 'santa',
                'content' => <<<'MD'
## Admin Settings

The Settings page is the central configuration hub for the entire application. Only Santas can access it. Every toggle, date, API key, and branding option lives here. This section describes every setting in detail so you know exactly what each one does and when to use it.

---

### Purpose

Settings control the behavior and appearance of the food drive application. They determine which public features are active (self-registration, adopt-a-tag), how delivery operations work (dates, time windows), what integrations are connected (Google OAuth, SMS, route optimization), and what the site looks like (logo, sponsor logos). Getting these right before the season starts is essential.

---

### Accessing Settings

Navigate to **Santa > Settings** from the top navigation bar. Settings are organized into sections on a single page with a sidebar for quick navigation.

---

### Public Features

#### Self-Registration
- **Toggle:** Enable or disable family self-registration
- **When enabled:** A "Register Your Family" link appears on the homepage. Families can fill out a form to register themselves and their children.
- **When disabled:** The registration link disappears. Visitors see a "Registration Closed" page with advisor contact information.
- **Best practice:** Enable self-registration in October/November when sign-ups open. Disable it a week before delivery day when the cutoff passes.

#### Adopt-a-Tag
- **Toggle:** Enable or disable the Adopt-a-Tag online portal
- **Deadline:** The date by which adopters must return gifts (e.g., "December 10, 2025"). This appears on the portal and on printed gift tags.
- **Custom Message:** Optional text displayed on the Adopt-a-Tag portal page (e.g., "Thank you for supporting Granite Falls families!")
- **When enabled:** A public `/adopt` page is available where community members browse and claim gift tags. QR codes on printed tags link to the portal.
- **When disabled:** The portal is inaccessible. QR codes on tags link to the warehouse scan URL instead.

#### Family Status Pages
- **Toggle:** Enable or disable public family status pages
- **When enabled:** Each family gets a unique URL where they can check their delivery status without logging in. Useful for families who want to know when their delivery is coming.
- **When disabled:** Status page URLs return a 404.

---

### Operations

#### Season Year
- **Field:** A year number (e.g., 2025)
- **What it controls:** The "current season" — which families, children, and data are shown throughout the system. Only families tagged with the current season year are visible in day-to-day operations.
- **Changing it:** When you archive a season, the year automatically increments. You can also change it manually.
- **Important:** Changing the season year changes what data everyone sees across the entire application. Do not change it mid-season.

#### Delivery Dates
- **Field:** Comma-separated list of delivery dates (e.g., "December 18th,December 19th")
- **What it controls:** These dates appear on delivery sheets, the family detail page, and documents. Families can be assigned to specific dates.
- **Multiple dates:** The food drive can span multiple days. Each date is a separate option when assigning families.

#### Delivery Time Range
- **Field:** Start time and end time (e.g., 8:00 AM to 9:00 PM)
- **What it controls:** The allowed delivery window. Appears on delivery sheets and the Driver View to let families know when to expect their delivery.

#### Delivery Sheet Footer
- **Return-to Role:** What role drivers should return to after delivery (e.g., "System Engineers")
- **Return-to Name:** Specific person or group to return to
- **HS Phone Number:** The high school or main contact phone number printed on delivery sheet footers

#### Coordinator Positions
- **Field:** Comma-separated list of position titles (e.g., "System Engineer,Activities Coordinator,Food Manager,NINJA,Marketing Director")
- **What it controls:** The available positions shown in user profiles and the coordinator team page. These are organizational roles, separate from the system permission roles (Family/Coordinator/Santa).

#### Paper Size
- **Options:** Letter (US standard 8.5x11") or A4 (international)
- **What it controls:** The page size used when generating PDFs — gift tags, family summaries, delivery sheets.

---

### Branding

#### Site Logo
- **Upload:** Click to upload this year's logo image (PNG, JPG, max 2MB)
- **Where it appears:** Homepage header and as the browser favicon
- **Best practice:** Upload a new logo at the start of each season. The logo should be the current year's food drive logo.

#### Sponsor Logos
- **Upload:** Upload one or more sponsor logo images
- **URL:** Each sponsor logo can have an optional URL link
- **Where they appear:** Homepage sponsors section
- **Management:** You can add new logos, set URLs for each, and remove logos you no longer need
- **Best practice:** Get sponsor logos early and upload them before the season launches so the homepage looks professional from day one.

---

### Notifications

#### Email Notifications
- **Toggle:** Master switch for email sending
- **When enabled:** The system sends email notifications for configured events
- **When disabled:** No emails are sent regardless of other settings

#### SMS (Twilio)
SMS notifications require a Twilio account. Configure the following:

| Field | Description |
|-------|-------------|
| **SMS Enabled** | Master toggle for SMS sending |
| **Twilio SID** | Your Twilio Account SID (from the Twilio dashboard) |
| **Twilio Auth Token** | Your Twilio Auth Token |
| **Twilio From Number** | The phone number to send SMS from (must be a Twilio number) |

**SMS Triggers** — choose which events send an SMS:
| Trigger | Description |
|---------|-------------|
| **On Registration** | When a family self-registers, send confirmation SMS to their phone |
| **On Gift Adopted** | When someone adopts a gift tag for a family's child, notify the family |
| **On In Transit** | When the delivery team marks the family as "In Transit" |
| **On Delivered** | When delivery is marked as complete |

---

### Integrations

#### Google OAuth
- **Client ID** and **Client Secret** from the Google Cloud Console
- **How to set up:**
  1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
  2. Create a project (or select an existing one)
  3. Go to **APIs & Services > Credentials**
  4. Create an **OAuth 2.0 Client ID** (Web application)
  5. Set the authorized redirect URI to: `https://yourdomain.com/auth/google/callback`
  6. Copy the Client ID and Client Secret into the settings
- **Clearing:** To disable Google OAuth, clear both fields and save

#### OpenRouteService
- **API Key** from [openrouteservice.org](https://openrouteservice.org/)
- **What it does:** Powers the delivery route optimization feature. When you click "Optimize" on a route, the system sends stop coordinates to ORS and gets back the optimal driving order.
- **Free tier:** ORS offers a free API tier with rate limits that are sufficient for the food drive's needs (typically a few dozen route optimizations per season).

#### Hints
- **Toggle:** Enable or disable contextual hint banners throughout the application
- **When enabled:** Helpful tips appear as blue info banners on various pages explaining new features or providing guidance
- **When disabled:** Hint banners are hidden for a cleaner interface

---

### How Settings Connect to Other Features

| Setting | Affects |
|---------|---------|
| Self-Registration | Homepage, public registration form |
| Adopt-a-Tag | Gift tags (QR codes), public adoption portal, tag printing exclusions |
| Family Status | Family detail page (token generation), public status pages |
| Season Year | All data views — families, children, shopping lists, warehouse, reports |
| Delivery Dates | Delivery sheets, family assignment, Driver View |
| Paper Size | All PDF generation — gift tags, family summaries, delivery sheets |
| Google OAuth | Login page, access request flow |
| OpenRouteService | Route Builder optimize button |
| Twilio | SMS notifications throughout the delivery lifecycle |
| Site Logo | Homepage, favicon |
| Sponsor Logos | Homepage sponsors section |

---

### Tips & Best Practices

- **Configure settings at the start of the season** — do not wait until delivery week. Set the season year, delivery dates, logo, and public features early.
- **Test SMS before relying on it.** Send a test by enabling SMS on Registration and submitting a test self-registration with your own phone number.
- **Keep the OpenRouteService API key** — it is reusable across seasons. No need to get a new one each year.
- **Save frequently.** The settings page has one Save button at the bottom. If you navigate away without saving, changes are lost.
- **Disable self-registration** when the signup window closes. This prevents late registrations that cannot be fulfilled.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| Settings do not save | Make sure you scroll to the bottom and click **Save Settings**. Check for validation errors highlighted in red. |
| SMS not sending | Verify all three Twilio fields are filled in (SID, Token, From Number). Make sure the From Number is a valid Twilio phone number. Check Twilio's dashboard for error logs. |
| Google OAuth "redirect_uri_mismatch" | The redirect URI in Google Cloud Console must exactly match your site's URL: `https://yourdomain.com/auth/google/callback`. Watch for http vs https and trailing slashes. |
| Logo not appearing after upload | Clear your browser cache. The logo is stored with a fixed filename and the browser may be caching the old one. |
| Route optimization says "API key missing" | Enter your OpenRouteService API key in Settings > Integrations and save before using the Route Builder. |
| Season year changed accidentally | Change it back to the correct year immediately. Changing the season year affects what data everyone sees. |

---

### FAQ

**Q: Do I need to reconfigure settings every year?**
A: Most settings carry over when the season year changes. You will need to update: delivery dates, Adopt-a-Tag deadline, and the site logo. API keys and Twilio credentials persist.

**Q: Can I have different settings for different schools?**
A: No. Settings are global — they apply to the entire food drive. School-specific differences are handled through school ranges and family number assignments.

**Q: What happens if I enable a feature mid-season (e.g., Adopt-a-Tag)?**
A: It takes effect immediately. The Adopt-a-Tag portal becomes available as soon as you toggle it on and save. Tags that were already printed will have QR codes pointing to the portal.
MD
            ],
            [
                'slug' => 'legacy-import',
                'title' => 'Legacy Import (Access DB)',
                'icon' => 'database',
                'role' => 'santa',
                'content' => <<<'MD'
## Legacy Import (Access DB)

The Legacy Import system allows you to bring in historical data from the Microsoft Access databases that were used to run the food drive in previous years. This is important for building a multi-year archive, analyzing trends, and referencing past data. This section explains what the legacy files are, how the import process works, and how to troubleshoot common problems.

---

### Purpose

Before this web application existed, the GFSD Food & Gift Drive was managed using Microsoft Access databases. Each year had its own `.accdb` or `.mdb` file containing family and child records. The Legacy Import feature reads those files and brings the data into the new system, creating season archive records so you can look back at historical data and see trends over the years (e.g., how many families were served in 2018 vs. 2023).

---

### Understanding the Access Database Files

The legacy system used a split-database architecture with two types of files:

| File Type | Suffix | Contains | Can Import? |
|-----------|--------|----------|-------------|
| **Backend** | `_be` | Actual data tables (Family Table, Child Table, etc.) | **Yes — use this one** |
| **Frontend/Admin** | `_fe` | Forms, queries, reports, macros — no raw data | **No — skip this** |

**File extensions:**
- `.accdb` — Microsoft Access 2007+ format
- `.mdb` — Microsoft Access 2003 and earlier format

Both formats are supported by the import system.

**Where to find the files:**
Legacy database files may be stored on a shared drive, USB stick, or in the application's built-in legacy archive at `.claude/Legacy DBS/`. The archive is organized by year (e.g., `.claude/Legacy DBS/2018/`, `.claude/Legacy DBS/2019/`).

---

### Import Methods

There are three ways to import legacy data:

#### Method 1: Upload and Import a Single File
1. Go to **Santa > Seasons > Import**
2. Click **Choose File** and select the `_be` Access database file
3. Set the **Season Year** to match the year of the data (e.g., 2019)
4. Click **Upload**
5. You will see a **Table Picker** showing all tables in the Access database
6. Click **Import All** to import Family Table and Child Table in one step
7. Or click individual tables to preview them first, then import selectively

#### Method 2: Import Pre-Loaded Legacy Files
If legacy files are stored in the `.claude/Legacy DBS/` directory:
1. Go to **Santa > Seasons > Import**
2. Scroll down to the **Legacy Databases** section
3. You will see files organized by year, with the main `_be` database highlighted
4. Click the **Import** button next to a file
5. For Access files, you will be taken to the Table Picker
6. For Excel files, you will see a preview of the data

#### Method 3: Bulk Import All Legacy Files
1. Go to **Santa > Seasons > Import**
2. Click **Import All Legacy Databases**
3. The system scans all year folders, finds the main `_be` file in each, and imports all of them in sequence
4. Years that already have a Season record are skipped automatically
5. This can take several minutes for many years of data

---

### How the Import Process Works

When you import an Access database, the system:

1. **Opens the database** using the `mdbtools` driver (on Linux/Mac) or the ODBC driver (on Windows)
2. **Lists all tables** in the database
3. **Reads the Family Table** — maps columns like "Family Name", "Address", "Phone Number", etc. to the new schema
4. **Creates Family records** with `season_year` set to the target year
5. **Reads the Child Table** — maps columns like "First Name", "Age", "Gender", "Shirt Size", etc.
6. **Links children to families** using an internal ID mapping:
   - The Access database uses an auto-increment "Family ID" to link children to families
   - The import reads the Family Table to build a map: Access Family ID → Family Number → our database ID
   - Children are then linked to the correct family in our system
7. **Creates a Season record** with computed statistics (total families, total children, etc.)

---

### Column Mapping

The import system automatically maps legacy column names to the new schema. It handles variations in naming across different years' databases:

| New Field | Legacy Column Names Recognized |
|-----------|-------------------------------|
| family_name | Family Name, FamilyName, Last Name |
| address | Address, Street Address, StreetAddress |
| phone1 | Phone Number, Phone, Phone1, Primary Phone |
| phone2 | Phone 2, Phone2, Secondary Phone, Alt Phone |
| family_number | Family Number, FamilyNumber, Number |
| number_of_adults | Number of Adults, Adults, NumAdults |
| number_of_family_members | Family Members, Total Members, NumberOfMembers |
| delivery_preference | Delivery Preference, Delivery, DeliveryMethod |
| delivery_status | Delivery Status, Status, DeliveryStatus |

Child columns follow a similar pattern (First Name, Age, Gender, Shirt Size, Pant Size, Shoe Size, Toy Ideas, etc.).

---

### Previewing Before Import

For any table, you can preview the data before importing:

1. On the Table Picker, click a table name
2. Choose the **Type** (Family or Child)
3. Click **Preview**
4. You will see a table showing the first several rows with column mapping information
5. Review the data to make sure it looks correct
6. Click **Import** to proceed or **Back** to return to the table picker

This is useful for unfamiliar databases where you are not sure which table contains which data.

---

### Excel Import

In addition to Access databases, the import system also supports Excel files (`.xlsx`, `.xls`):

1. Upload the Excel file on the Import page
2. Choose the type: **Family** or **Child**
3. Preview the mapped data
4. Import

Excel imports use the same column mapping logic as Access imports.

---

### After Import: Season Records

After a successful import, a **Season record** is created (or updated) in the Seasons archive. You can view it at **Santa > Seasons**. Each season record shows:
- Year
- Total families
- Total children
- Total members
- Archive date
- Notes

The Seasons page also shows a year-over-year chart of families and children served.

---

### How Legacy Import Connects to Other Features

- **Seasons** — imports create season archive records for historical tracking
- **Family Management** — imported families appear in the family list when the season year matches
- **Reports** — historical data can be referenced for year-over-year comparisons

---

### Tips & Best Practices

- **Always use the `_be` file.** The `_fe` file contains no importable data. Look for filenames containing "database_be" or "FoodDriveDatabase".
- **Import families before children.** When using selective import, always import the Family Table first, then the Child Table. Children need families to link to.
- **Use "Import All" for convenience.** It handles the correct order (families first, then children) automatically.
- **Set the correct season year** before importing. If you accidentally import data under the wrong year, you will need to delete the families and re-import.
- **Check the import count** after completion. The success message tells you how many records were imported and how many were skipped. If 0 families were imported, you likely used the wrong file.
- **Bulk import early.** Run the "Import All Legacy Databases" at the beginning of the project to build the full historical archive in one shot.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| 0 families imported | You are likely using the `_fe` (frontend) file instead of the `_be` (backend) file. Check the filename. |
| "No tables found" error | The file may be corrupted or in an unsupported format. Try opening it in Microsoft Access first to verify it works. |
| Children not linked to families | The Child Table uses "Family ID" (an Access internal ID) to link to families. If the Family Table was not imported first, or if the family ID mapping failed, children will be unlinked. Re-import starting with the Family Table. |
| Duplicate families after re-import | The import does not deduplicate. If you import the same file twice, you will get duplicate records. Delete the duplicates using the Duplicates tool or archive and reimport the season. |
| Import takes a very long time | Large databases (500+ families) take longer. The bulk import sets no time limit but may take several minutes. Be patient and do not navigate away. |
| Delivery status values look wrong | The import normalizes status values (e.g., "DELIVERED" becomes "delivered"). If legacy data had non-standard values, they may not map correctly. Check and correct them manually. |
| Excel file import fails | Verify the Excel file has column headers in the first row. The import reads the first row as column names for mapping. |

---

### FAQ

**Q: Can I import the same year twice?**
A: You can, but it will create duplicate records. The bulk import skips years that already have a Season record, but individual imports do not. If you need to redo an import, consider archiving the season first.

**Q: Do I need Microsoft Access installed to import?**
A: No. The application reads Access files directly using server-side libraries. You do not need Access on the server or your computer.

**Q: Will imported data appear in the current season?**
A: Only if the import's season year matches the current season year in Settings. Imported historical data (e.g., 2019) will only be visible when viewing that season's archive.
MD
            ],
            [
                'slug' => 'warehouse',
                'title' => 'Warehouse & Inventory',
                'icon' => 'archive',
                'role' => 'coordinator',
                'content' => <<<'MD'
## Warehouse & Inventory

The Warehouse module is the inventory management system for the food drive. It tracks every incoming item — food boxes, canned goods, gifts, baby supplies, household items — from the moment they arrive until they are assigned to a family. This section covers the Dashboard, Receiving workflow, Gift Drop-Off, Kiosk Mode, Inventory browsing, and the Transaction audit log.

---

### Purpose

During the food drive, items come in from many sources: school drives bring in canned goods, community members drop off adopted gifts, NINJAs return from shopping trips, businesses donate bulk supplies. Without tracking, you cannot answer basic questions: "Do we have enough canned corn?" "Which families still need gifts?" "How many items came from the school drive vs. community donations?"

The Warehouse module answers all of these questions by logging every item as it comes in and comparing totals against what is needed based on the number of families and children.

---

### Dashboard

Navigate to **Warehouse** in the top navigation bar to see the Dashboard.

The Dashboard has four sections:

#### Deficit Table
A table showing each warehouse category (e.g., Canned Vegetables, Canned Fruit, Gifts - Boys 5-8, Baby Supplies) with:
- **Needed** — how many of this category are required based on family/child counts
- **On Hand** — how many have been received
- **Deficit/Surplus** — the difference, color-coded:
  - **Red** = deficit (still need more)
  - **Green** = surplus (have enough or extra)

This is the most important view for the Food Manager — it answers "what do we still need?" at a glance.

#### Gift Progress
Bar charts showing gift coverage by age and gender group (e.g., Boys 5-8, Girls 9-12). Each bar shows what percentage of children in that group have received gifts at various levels (None, Partial, Moderate, Full).

#### Donation Sources
A breakdown of where items came from:
- **School Drive** — collected at school events
- **Adopt-a-Tag** — returned by tag adopters
- **Community Donation** — drop-offs from community members
- **Store Purchase** — bought by NINJAs or coordinators

Shows counts and percentages per source.

#### Live Feed
The 20 most recent warehouse transactions, showing:
- Timestamp
- What was received (item name and category)
- Quantity
- Source
- Who logged it

The live feed **auto-refreshes every 15 seconds** so you always see the latest activity without reloading the page.

---

### Receiving Items

The Receiving workflow is how items get logged into the warehouse. It is designed for speed — especially with a USB barcode scanner for continuous scanning.

**Step-by-step with a barcode scanner:**
1. Navigate to **Warehouse > Kiosk** (the Receive page redirects here automatically)
2. The cursor is focused on the barcode input field
3. **Scan a barcode** with your USB scanner
4. The system looks up the barcode:
   - **Known barcode** → the item name, category, and default quantity auto-fill. Just click **Record** (or scan the next item).
   - **Unknown barcode** → the system tries an external UPC database lookup. If found, it suggests the item name. You then select the category and quantity manually.
   - **No match** → manually select a category, enter the item name and quantity.
5. Choose a **Source**:

| Source | When to Use |
|--------|-------------|
| School Drive | Items collected at school donation drives |
| Adopt-a-Tag | Gifts returned by adopters (not specific to a child — use Gift Drop-Off for child-specific gifts) |
| Community Donation | General donations from community members, businesses, churches |
| Store Purchase | Items bought by NINJAs, coordinators, or with drive funds |

6. Optionally enter a **Donor Name** (e.g., "Mountain Way Elementary 3rd Grade")
7. Optionally enter a **Volunteer Name** — the name of the person doing the scanning (useful in Kiosk Mode where volunteers may not be logged in)
8. Click **Record**
9. The item is logged, a success notification appears, and the input re-focuses for the next scan

**Manual entry (no scanner):**
If you do not have a barcode scanner, you can:
1. Type a barcode number manually in the barcode field, or
2. Skip the barcode field entirely and use the category buttons to select what you are receiving
3. Enter the quantity
4. Click **Record**

---

### Gift Drop-Off

Gift Drop-Off is a special receiving workflow for when an adopter returns a gift for a **specific child**. Unlike general receiving (which logs items by category), Gift Drop-Off links the gift directly to the child's record and updates their gift coverage level.

**Step-by-step:**
1. Navigate to **Warehouse > Gift Drop-Off** and enter the child's family number, OR scan the **QR code** from the gift tag (this takes you directly to the drop-off page for that child)
2. The child's information is displayed:
   - Family number and family name
   - Child's name, gender, and age
   - Clothing sizes and gift preferences
   - Current gift level
3. Verify this is the correct child (check the family number on the gift against the screen)
4. Click **Accept Gift**
5. The system:
   - Creates a warehouse transaction recording the gift drop-off
   - Automatically bumps the child's gift level to at least **Moderate** (level 2)
   - Returns a confirmation

**Gift Levels:**
| Level | Name | Meaning |
|-------|------|---------|
| 0 | None | No gifts received for this child |
| 1 | Partial | Some gifts received but incomplete |
| 2 | Moderate | A reasonable set of gifts received |
| 3 | Full | Child is fully covered — no more gifts needed |

Gift Drop-Off always sets the level to at least Moderate. If the level was already Full, it stays at Full.

---

### Kiosk Mode

Kiosk Mode is a simplified, dark-themed version of the Receiving page designed for volunteers (NINJAs) who are doing intake scanning at a warehouse table. It strips away the app navigation and focuses entirely on the scanning workflow.

**Step-by-step:**
1. Navigate to **Warehouse > Kiosk** (or have a link/bookmark ready on the scanning station)
2. The page opens with a dark theme and large, easy-to-read buttons
3. **Scan barcodes** or **tap category buttons** to log items
4. The screen shows:
   - The **last 5 scans** so the volunteer can verify what just came in
   - **Running session totals** — how many items have been logged in this session
5. The volunteer can enter their name so transactions are attributed to them (even without a user account)
6. To exit Kiosk Mode, click **Exit Kiosk** in the top corner

**Setting up a kiosk station:**
1. Place a laptop or tablet at the intake table
2. Connect a USB barcode scanner to the laptop
3. Open the browser and navigate to the Kiosk URL
4. Full-screen the browser (F11)
5. The volunteer can start scanning immediately

**Multiple kiosk stations:** You can run as many kiosk stations as you have devices. Each station logs transactions independently and they all appear in the live feed and dashboard.

---

### Inventory Page

The Inventory page provides a detailed view of everything in the warehouse.

**Navigate to:** **Warehouse > Inventory**

**What you see:**
- Each warehouse category listed with:
  - Category name and icon
  - Needed vs. on-hand counts with deficit/surplus indicator
- **Expandable items** — click a category to see individual items within it (e.g., under "Canned Vegetables": Green Beans x 47, Corn x 52, Peas x 31)
- **Type filters** — filter by item type (Food, Gift, Baby, Household, etc.)

This page is useful for detailed auditing — when you need to know not just "do we have enough canned vegetables?" but "specifically how many cans of green beans vs. corn?"

---

### Transaction Audit Log

Every warehouse action is recorded as a transaction. The full log is available at **Warehouse > Transactions**.

**Filters available:**
| Filter | Description |
|--------|-------------|
| **Type** | Transaction type (receipt, gift drop-off, adjustment) |
| **Category** | Filter by warehouse category |
| **Source** | Filter by donation source (School Drive, Adopt-a-Tag, etc.) |
| **Search** | Search by donor name or barcode |
| **Date From / Date To** | Date range filter |

**Each transaction shows:**
- Date and time
- Item name and category
- Quantity
- Source
- Donor name (if entered)
- Barcode scanned (if applicable)
- Who logged it (user or volunteer name)
- IP address (for kiosk accountability)

Transactions are paginated (50 per page) and sorted newest-first.

---

### How the Warehouse Connects to Other Features

- **Family Management** — the number of families and children determines how many items are "needed" in the deficit table
- **Gift Tags / Adopt-a-Tag** — QR codes on tags link to Gift Drop-Off for easy intake
- **Command Center** — warehouse statistics feed into the Overview Mode
- **Dashboard** — the Coordinator dashboard shows gift coverage stats derived from warehouse data
- **Delivery Day** — families should have their items packed before delivery begins

---

### Tips & Best Practices

- **Set up kiosk stations early** and test them before the big intake days. Make sure scanners work, the network is stable, and volunteers know how to use the interface.
- **Use a dedicated scanning laptop** that stays at the intake table. Do not use a shared computer that people might navigate away from.
- **Train volunteers on Kiosk Mode** — show them how to scan, how to manually select a category when a barcode is not recognized, and how to enter donor names.
- **Check the deficit table throughout the day** as items come in. The Food Manager should monitor it to know when they have enough of each category.
- **Use the live feed** to spot errors quickly. If someone accidentally logs 100 cans instead of 10, you will see it in the feed.
- **Log donor names** for significant donations. This helps with thank-you letters and sponsor recognition after the drive.
- **Use the Gift Drop-Off workflow** for adopted gifts instead of general receiving. This properly links the gift to the child and updates their coverage level.
- **Print QR codes bigger** if adopters are going to scan them at drop-off. The standard gift tag QR code works, but a larger printout at the drop-off station can speed up the process.

---

### Common Issues & Troubleshooting

| Problem | Solution |
|---------|----------|
| Barcode scanner not working | Make sure the scanner is plugged in and the cursor is focused on the barcode input field. Most USB scanners emulate keyboard input — if the cursor is not in the right field, the barcode text goes nowhere. Click on the barcode field first. |
| Barcode scanned but "unknown item" | Not all barcodes are in the system's database. Select the category manually and enter the item name. The barcode is still logged for future recognition. |
| Deficit numbers seem wrong | Verify that family counts and children counts are correct. The deficit table calculates "needed" based on the current season's data. Also check that the warehouse categories have the correct "per family" or "per child" formulas. |
| Gift Drop-Off shows wrong child | Verify the family number. If the QR code is from a different season or the child was deleted and re-added, the link may be stale. Search for the child by family number instead. |
| Kiosk mode accidentally exited | Navigate back to the Kiosk URL or use the browser's back button. No data is lost — all transactions were saved as they were scanned. |
| Transactions are missing | Check the filters on the Transactions page. A filter might be hiding the records you are looking for. Clear all filters and search again. |
| Live feed not updating | The feed auto-refreshes every 15 seconds. If it seems stuck, check your internet connection. Refresh the page if needed. |

---

### FAQ

**Q: Can I undo a transaction?**
A: There is no built-in undo button. If an item was logged incorrectly, you can note it in the audit log. For significant errors, a Santa may need to adjust the data directly.

**Q: Do volunteers need user accounts to use Kiosk Mode?**
A: No. Kiosk Mode works for anyone with access to the URL. Volunteers can enter their name in the "Volunteer Name" field so their scans are attributed to them. The IP address is also logged for accountability.

**Q: Can I use a phone camera as a barcode scanner?**
A: The current system is optimized for USB barcode scanners that emulate keyboard input. Phone camera scanning is not natively supported, but you can use a third-party barcode scanning app that copies the barcode text to the clipboard, then paste it into the barcode field.
MD
            ],
        ];
    }
}
