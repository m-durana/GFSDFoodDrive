# GFSDFoodDrive - STILL W.I.P

The GFSD Food Drive Family Management System helps coordinate the GFSD FoodDrive. 
- family registration
- child demographics tracking
- delivery coordination
- volunteer management.
  
This project is a Laravel port of a legacy PHP application.

## Tech Stack
- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL
- **Frontend:** Laravel Blade templates + Tailwind CSS (via Vite)
- **Authentication:** Laravel Breeze (username-based)
- **Permissions:** spatie/laravel-permission

## Role System
Role-based access control system:
- **Family:** Can add and view their own families and children.
- **Coordinator:** Can view all families and access coordinator dashboards.
- **Santa (Admin):** Full admin access: user management, number assignment, and delivery coordination.

## Development Setup

1. Install PHP 8.2+ and [Composer](https://getcomposer.org/download/), then install dependencies:
   ```bash
   composer install
   npm install
   ```

2. Configure `.env`:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    *Note: Update the `.env` file with your database credentials.*

3. Set up database schema:
    ```bash
    php artisan migrate
    ```

4. *(Optional)* Import legacy data from the original PHP application:
    ```bash
    # LEGACY_DB_* variables have to be set in your .env first
    php artisan db:seed --class=LegacyDataSeeder
    ```

5. Start the development servers:
    ```bash
    php artisan serve
    npm run dev
    ```

6. Add an admin user:
    ```bash
    php artisan tinker
    App\Models\User::create(['username' => 'santa', 'first_name' => 'Admin', 'last_name' => 'User', 'password' => bcrypt('password'), 'permission' => 9]);
    ```

7. Visit https://localhost:8000 and log in with `santa` and `password`
