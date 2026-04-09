@echo off
:: ================================================
:: DOLE Payroll - Automated XAMPP Setup Script
:: ================================================

echo.
echo ================================================
echo  DOLE Payroll - XAMPP Setup
echo ================================================
echo.

:: Auto-detect XAMPP installation
echo [1/5] Detecting XAMPP installation...

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
    echo Please install XAMPP or set XAMPP_PATH environment variable.
    pause
    exit /b 1
)

:: Check if XAMPP services are running
echo.
echo [2/5] Checking XAMPP services...

:: Check Apache
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I "httpd.exe" >NUL
if %ERRORLEVEL%==0 (
    echo [INFO] Apache is already running
) else (
    echo [INFO] Starting Apache...
    start "" "%XAMPP_PATH%\apache\bin\httpd.exe"
    timeout /t 3 >nul
)

:: Check MySQL
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL
if %ERRORLEVEL%==0 (
    echo [INFO] MySQL is already running
) else (
    echo [INFO] Starting MySQL...
    start "" "%XAMPP_PATH%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_PATH%\mysql\bin\my.ini"
    timeout /t 5 >nul
)

:: Wait for MySQL to be ready
echo [INFO] Waiting for MySQL to be ready...
:wait_mysql
"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root -e "SELECT 1;" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    timeout /t 2 >nul
    goto :wait_mysql
)

:: Create database if it doesn't exist
echo.
echo [3/5] Creating database...
"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS dole_payroll;" 2>nul
if %ERRORLEVEL%==0 (
    echo [SUCCESS] Database 'dole_payroll' created/verified
) else (
    echo [ERROR] Failed to create database
    pause
    exit /b 1
)

:: Check for existing data and backup if needed
echo.
echo [4/5] Checking for existing data...
call backup-data.bat

:: Run Laravel migrations (data-safe approach)
echo.
echo [4/5] Running Laravel migrations...
php artisan migrate --force
if %ERRORLEVEL%==0 (
    echo [SUCCESS] Migrations completed
    
    # Only restore data if migrations actually ran (not "Nothing to migrate")
    php artisan migrate:status | find "No" >nul
    if %ERRORLEVEL% neq 0 (
        # Migrations ran, so restore data if backup was created
        if exist "data-backups\*.sql" (
            echo [INFO] Restoring data after migration...
            call restore-data.bat
        )
    ) else (
        echo [INFO] No migration changes needed - skipping data restore
    )
) else (
    echo [WARNING] Migrations failed or already completed
)

:: Import initial data if needed
echo.
echo [5/5] Checking for initial data import...
if exist "dole_payroll.sql" (
    echo [INFO] Found dole_payroll.sql, importing data...
    "%XAMPP_PATH%\mysql\bin\mysql.exe" -u root dole_payroll < dole_payroll.sql
    if %ERRORLEVEL%==0 (
        echo [SUCCESS] Data imported successfully
    ) else (
        echo [WARNING] Data import failed (might already exist)
    )
) else (
    echo [INFO] No dole_payroll.sql found, skipping import
)

echo.
echo ================================================
echo [SUCCESS] XAMPP setup completed!
echo ================================================
echo.
echo Next steps:
echo 1. Run 'php artisan serve' to start Laravel
echo 2. Access app at http://localhost:8000
echo.
pause
