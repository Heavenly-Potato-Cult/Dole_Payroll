## Database Workflow Commands:

### Automated Workflows:
push-to-gdrive.bat - XAMPP -> Project -> Google Drive (complete push)
pull-from-gdrive.bat - Google Drive -> Project -> XAMPP (complete pull)

### Individual Steps:
### XAMPP -> Project:
force-import-sql.bat - Import XAMPP MySQL to project SQL file

### Project -> Google Drive:
gdrive-upload.bat - Upload project SQL to Google Drive

### Google Drive -> Project:
gdrive-download.bat - Download from Google Drive to project SQL

### Project -> XAMPP:
force-import-sql.bat - Import project SQL to XAMPP MySQL

## For Setup & Data Management:
setup-xampp.bat - Automated XAMPP setup
backup-data.bat - Backup existing data
restore-data.bat - Restore from backups

## Documentation:
README-SETUP.md - Complete setup guide

## Laravel Files:
database/seeders/ProductionDataSeeder.php - Data preservation seeder

## ===Concise===
## Google Drive Workflow:
push-to-gdrive.bat - Automated push
pull-from-gdrive.bat - Automated pull
gdrive-upload.bat - Individual upload
gdrive-download.bat - Individual download
gdrive-list.bat - List files
get-rclone.bat - Get rclone tool

## Setup & Data Management:
setup-xampp.bat - XAMPP setup
backup-data.bat - Backup data
restore-data.bat - Restore data