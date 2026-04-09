@echo off
:: ================================================
:: DOLE Payroll - Upload to Google Drive
:: ================================================

echo Uploading database to Google Drive...

:: Check if rclone exists
if not exist "rclone.exe" (
    echo ERROR: rclone.exe not found!
    echo Please run: .\setup-rclone.bat first
    pause
    exit /b 1
)

:: Check if database file exists
if not exist "dole_payroll.sql" (
    echo ERROR: dole_payroll.sql not found!
    echo Please export your database first
    pause
    exit /b 1
)

:: Create folder and upload to Google Drive
echo Creating folder and uploading dole_payroll.sql to Google Drive...
rclone.exe mkdir gdrive:DOLE_Payroll_Database
rclone.exe copy dole_payroll.sql gdrive:DOLE_Payroll_Database/

if %ERRORLEVEL%==0 (
    echo SUCCESS: Database uploaded to Google Drive!
) else (
    echo ERROR: Upload failed!
)

pause
