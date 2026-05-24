# GFSDFoodDrive

Family management + logistics for the Granite Falls School District annual Food &
Gift Drive.

## Roles

Only Santa + the System Coordinator position see PII; everyone else works in family numbers.

- **Santa** (`santa`): full admin. Can sudoer any user for temporary Santa-equivalent access.
- **Coordinator** (`coordinator`): permissions follow their **position**, which maps to sections (`giving-tree`, `food`, `packing`, `delivery`, `business`, `system`) via the `coordinator_section_map` Setting. The "System Engineer" position holds all sections + the PII flag.
- **Advisor** (`family`): school staff entering household data at intake. Scoped to their own school.
- **NINJA** (`ninja`): student volunteer. Logs in only for the Shopping Day kiosk + warehouse scanners.
- **Self-Service** (`self_service`): households self-registering via the public form.

## Development Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=TestDataSeeder   # optional: ~150 families of fake data
php artisan serve
npm run dev
```

Create an admin user, then visit http://localhost:8000:

```bash
php artisan tinker
App\Models\User::create(['username' => 'santa', 'first_name' => 'Admin', 'last_name' => 'User', 'password' => bcrypt('password'), 'permission' => 9]);
```
