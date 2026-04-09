@echo off
:: ================================================
:: DOLE Payroll - Data Backup Before Migration
:: ================================================

echo.
echo ================================================
echo  DOLE Payroll - Data Backup
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

:: Check if database has data
echo [2/3] Checking for existing data...

"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root -e "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'dole_payroll' AND table_name = 'employees';" > temp_count.txt 2>nul

set /p table_count=<temp_count.txt
del temp_count.txt

if %table_count%==0 (
    echo [INFO] No employees table found - database appears to be empty
    goto :skip_backup
)

"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root -e "SELECT COUNT(*) FROM dole_payroll.employees;" > temp_emp_count.txt 2>nul
set /p emp_count=<temp_emp_count.txt
del temp_emp_count.txt

if %emp_count%==0 (
    echo [INFO] No employee records found - database is empty
    goto :skip_backup
)

echo [INFO] Found %emp_count% employee records - creating backup...

:: Create backup directory
set BACKUP_DIR=%~dp0data-backups
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Generate timestamp for filename
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set TIMESTAMP=%datetime:~0,4%-%datetime:~4,2%-%datetime:~6,2%_%datetime:~8,2%%datetime:~10,2%%datetime:~12,2%
set BACKUP_FILE=%BACKUP_DIR%\data_backup_%TIMESTAMP%.sql

:: Backup the data
echo [3/3] Creating data backup...
"%XAMPP_PATH%\mysql\bin\mysqldump.exe" -u root --single-transaction --routines --triggers --no-create-info dole_payroll > "%BACKUP_FILE%"

if %ERRORLEVEL%==0 (
    echo [SUCCESS] Data backed up to: %BACKUP_FILE%
    
    :: Show backup info
    for %%F in ("%BACKUP_FILE%") do (
        echo Size: %%~zF bytes
    )
) else (
    echo [ERROR] Backup failed!
    pause
    exit /b 1
)

goto :end

:skip_backup
echo [INFO] Skipping backup - no data found

:end
echo.
echo ================================================
echo Backup process completed!
echo ================================================
echo.
pause
