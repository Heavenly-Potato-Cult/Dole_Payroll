@echo off
:: ================================================
:: DOLE Payroll - Aiven MySQL Backup Script
:: ================================================

:: Set backup directory
set BACKUP_DIR=%~dp0backups

:: Create backup folder if it doesn't exist
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Generate timestamp for filename
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set TIMESTAMP=%datetime:~0,4%-%datetime:~4,2%-%datetime:~6,2%_%datetime:~8,2%%datetime:~10,2%%datetime:~12,2%

:: Set backup filename
set BACKUP_FILE=%BACKUP_DIR%\backup_%TIMESTAMP%.sql

echo.
echo ================================================
echo  DOLE Payroll Database Backup
echo  Date: %TIMESTAMP%
echo ================================================
echo.
echo Connecting to Aiven and dumping database...

:: Check if password is set in environment variable
if "%DB_PASSWORD%"=="" (
    echo [ERROR] DB_PASSWORD environment variable is not set!
    echo Please set it with: set DB_PASSWORD=your_password
    pause
    exit /b 1
)

:: Run mysqldump from inside Docker container and save output locally
docker exec dole_payroll_app bash -c "mysqldump --host=dole-payroll-mysql-venardvibe-4523.e.aivencloud.com --port=12622 --user=avnadmin --password=%DB_PASSWORD% --ssl-ca=/var/www/certs/ca.pem defaultdb 2>/dev/null" > "%BACKUP_FILE%"

:: Check if backup was successful
if %ERRORLEVEL% == 0 (
    echo.
    echo [SUCCESS] Backup saved to:
    echo %BACKUP_FILE%
) else (
    echo.
    echo [FAILED] Backup failed! Check your Docker container is running.
    del "%BACKUP_FILE%" 2>nul
)

:: Delete backups older than 7 days
echo.
echo Cleaning up backups older than 7 days...
forfiles /p "%BACKUP_DIR%" /s /m *.sql /d -7 /c "cmd /c del @path" 2>nul
echo Done.

echo.
pause