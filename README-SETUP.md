# DOLE Payroll - XAMPP Setup Guide

## Quick Start (Automated)

### 1. Initial Setup
```bash
# Run the automated setup script
setup-xampp.bat
```

This script will:
- Auto-detect your XAMPP installation
- Start Apache and MySQL services
- Create the `dole_payroll` database
- Run Laravel migrations
- Import initial data from `dole_payroll.sql` (if available)

### 2. Start Laravel
```bash
php artisan serve
```

Access your app at: http://localhost:8000

## Database Management

### Pull Database from Google Drive
```powershell
# Download and import latest database from Google Drive
.\db-gdrive-pull.ps1
```

### Push Database to Google Drive
```powershell
# Export and upload database to Google Drive
.\db-gdrive-push.ps1
```

### Scheduled Backup
```bash
# Create scheduled backup (for Windows Task Scheduler)
gdrive-backup.bat
```

## Manual Setup (if automation fails)

### 1. XAMPP Requirements
- Install XAMPP from https://www.apachefriends.org/
- Ensure Apache and MySQL are running

### 2. Database Setup
```sql
-- Create database in phpMyAdmin
CREATE DATABASE dole_payroll;
```

### 3. Laravel Configuration
```bash
# Copy environment file
copy .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate
```

### 4. Import Data (optional)
```bash
# Import existing data
mysql -u root dole_payroll < dole_payroll.sql
```

## Environment Configuration

Your `.env` file should be configured for XAMPP:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dole_payroll
DB_USERNAME=root
DB_PASSWORD=

# Sessions, Cache, Queues
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Team Collaboration

### For New Team Members

1. Clone the repository
2. Run `setup-xampp.bat`
3. Start development with `php artisan serve`

### Database Sharing

1. **Export**: Use `db-gdrive-push.ps1` to share your changes
2. **Import**: Use `db-gdrive-pull.ps1` to get latest changes
3. **Backup**: Use `gdrive-backup.bat` for scheduled backups

## Google Drive Integration (Future)

The scripts are ready for Google Drive integration. To enable:

1. Get Google Drive API credentials
2. Update the configuration variables in the scripts:
   - `$GDRIVE_FOLDER_ID`
   - `$GDRIVE_CREDENTIALS`
3. Install Google Drive CLI tools or use Google Drive API

## Troubleshooting

### XAMPP Not Found
- Ensure XAMPP is installed in default locations
- Common paths: `C:\xampp`, `D:\xampp`, `C:\Program Files\xampp`

### Database Connection Issues
- Verify MySQL is running in XAMPP Control Panel
- Check that the database `dole_payroll` exists
- Verify `.env` configuration matches XAMPP setup

### Migration Errors
- Ensure database is created and empty
- Check MySQL credentials in `.env`
- Run `php artisan migrate:fresh` to reset

## File Structure

```
dole_payroll/
|-- setup-xampp.bat          # Automated XAMPP setup
|-- db-gdrive-pull.ps1       # Pull database from Google Drive
|-- db-gdrive-push.ps1       # Push database to Google Drive
|-- gdrive-backup.bat        # Scheduled backup script
|-- gdrive-dumps/            # Local database dumps folder
|-- .env.example             # Example environment configuration
|-- dole_payroll.sql         # Initial database dump (if available)
```

## Migration from Docker

If you're migrating from Docker:

1. Your Docker files are preserved (not deleted)
2. New scripts work with local XAMPP instead
3. Database operations now target local MySQL
4. Google Drive replaces Aiven for cloud storage
