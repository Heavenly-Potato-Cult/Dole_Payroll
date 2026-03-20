 Setup Instructions (After Cloning)
1. Clone the Repository
bashgit clone <repo-url> Dole_Payroll
cd Dole_Payroll

2. Install PHP Dependencies
bashcomposer install

3. Copy and Configure .env
bashcopy .env.example .env
Then open .env and update the database credentials:
envAPP_NAME="DOLE RO9 Payroll System"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dole_payroll
DB_USERNAME=root
DB_PASSWORD=          # leave blank if no password (default XAMPP)
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SANCTUM_STATEFUL_DOMAINS=localhost:8000

4. Generate Application Key
bashphp artisan key:generate

5. Create the Database
Open phpMyAdmin at http://localhost/phpmyadmin and create a new database:

Database name: dole_payroll
Collation: utf8mb4_unicode_ci

Or via XAMPP MySQL CLI:
bashC:\xampp\mysql\bin\mysql -u root -e "CREATE DATABASE dole_payroll CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

6. Run Migrations
bashphp artisan migrate
Expected tables created:

users, cache, jobs, sessions
personal_access_tokens (Sanctum)
roles, permissions, model_has_roles, etc. (Spatie)


7. Seed the Database
bashphp artisan db:seed
This will create:

All 7 roles: payroll_officer, hrmo, accountant, budget_officer, chief_admin_officer, ard, cashier
Default admin account (see credentials below)


8. Start the Development Server
bashphp artisan serve
Visit: http://localhost:8000



