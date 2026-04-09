# Team Setup Guide for DOLE Payroll

This guide helps new team members set up the DOLE Payroll project with Google Drive integration.

## Prerequisites

1. **XAMPP** installed and running
2. **Git** installed
3. **Google account** with Google Drive access

## Quick Setup Steps

### 1. Clone and Setup Project

```bash
git clone <repository-url>
cd Dole_Payroll
composer install
npm install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dole_payroll
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 3. Setup XAMPP Database

```bash
# Automated setup (recommended)
.\setup-xampp.bat

# Or manual setup:
# 1. Start Apache and MySQL in XAMPP
# 2. Create database "dole_payroll" in phpMyAdmin
# 3. Run migrations: php artisan migrate
```

### 4. Setup Google Drive Integration

**Simple Setup (Recommended):**

Since the potato has already set up the Google Cloud Project and shared Google Drive folder:

1. **Get rclone.exe** (one-time setup):
   ```bash
   .\get-rclone.bat
   ```
   *This automatically downloads and sets up rclone.exe for you*

2. **Configure rclone** (one-time setup):
   ```bash
   rclone config
   ```
3. **Follow these prompts**:
   - Name: `gdrive`
   - Storage: `18` (Google Drive)
   - Scope: `1` (Full access)
   - Leave client ID/secret blank
   - Auto config: `y` (Yes)
   - Browser opens - sign in to your Google account
   - Copy verification code back
   - Confirm: `y` (Yes)
   - Quit: `q`

**Important:** Each teammate needs to download rclone.exe once and place it in the project root. It's not included in Git because it's a large binary file.

### 5. Test Google Drive Workflow

```bash
# Pull latest database from Google Drive
.\pull-from-gdrive.bat

# Push your database to Google Drive
.\push-to-gdrive.bat
```

===END HERE BRU===


