@echo off
:: ================================================
:: DOLE Payroll - Data Restore After Migration
:: ================================================

echo.
echo ================================================
echo  DOLE Payroll - Data Restore
echo  Date: %date% %time%
echo ================================================
echo.

:: Auto-detect XAMPP installation
echo [1/3] Detecting XAMPP installation...

set XAMPP_FOUND=0
set XAMPP_PATHS="C:\xampp" "D:\xampp" "C:\Program Files\xampp" "D:\Program Files\xampp"

for %%P in (%XAMPP_PATHS%) do (
    if exist "%%P\mysql\bin\mysql.exe" (
        set XAMPP_PATH=%%P
        set XAMPP_FOUND=1
        echo [SUCCESS] XAMPP found at: %%P
        goto :xampp_found
    )
)

:xampp_found
if %XAMPP_FOUND%==0 (
    echo [ERROR] XAMPP not found in common locations!
    pause
    exit /b 1
)

:: Find latest backup file
echo [2/3] Finding latest backup...

set BACKUP_DIR=%~dp0data-backups
if not exist "%BACKUP_DIR%" (
    echo [ERROR] No backup directory found!
    echo Please run backup-data.bat first.
    pause
    exit /b 1
)

:: Find the most recent backup file
set LATEST_BACKUP=
for /f "delims=" %%F in ('dir /b /o-d "%BACKUP_DIR%\data_backup_*.sql" 2^>nul') do (
    set LATEST_BACKUP=%BACKUP_DIR%\%%F
    goto :found_backup
)

:found_backup
if not defined LATEST_BACKUP (
    echo [ERROR] No backup files found!
    echo Please run backup-data.bat first.
    pause
    exit /b 1
)

echo [INFO] Found backup: %LATEST_BACKUP%

:: Show backup info
for %%F in ("%LATEST_BACKUP%") do (
    echo Size: %%~zF bytes
    echo Modified: %%~tF
)

:: Restore the data with INSERT IGNORE to handle duplicates
echo [3/3] Restoring data...
:: Create a temporary restore script that uses INSERT IGNORE
echo SET FOREIGN_KEY_CHECKS=0; > temp_restore.sql
type "%LATEST_BACKUP%" >> temp_restore.sql
echo SET FOREIGN_KEY_CHECKS=1; >> temp_restore.sql

:: Replace INSERT with INSERT IGNORE to handle duplicates
powershell -Command "(Get-Content temp_restore.sql) -replace 'INSERT INTO', 'INSERT IGNORE INTO' | Set-Content temp_restore_fixed.sql"

"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root dole_payroll < temp_restore_fixed.sql

:: Cleanup temp files
del temp_restore.sql 2>nul
del temp_restore_fixed.sql 2>nul

if %ERRORLEVEL%==0 (
    echo [SUCCESS] Data restored successfully!
    
    :: Verify restoration
    "%XAMPP_PATH%\mysql\bin\mysql.exe" -u root -e "SELECT COUNT(*) as restored_employees FROM dole_payroll.employees;" 2>nul
    
    echo.
    echo [INFO] Data verification completed.
) else (
    echo [ERROR] Data restore failed!
    pause
    exit /b 1
)

echo.
echo ================================================
echo Restore process completed!
echo ================================================
echo.
pause
