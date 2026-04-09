@echo off
:: ================================================
:: DOLE Payroll - Automated Push to Google Drive
:: XAMPP -> Project -> Google Drive
:: ================================================

echo.
echo ================================================
echo  Automated Push: XAMPP -> Project -> Google Drive
echo ================================================
echo.

echo [1/3] Exporting from XAMPP MySQL to project SQL...
"C:\xampp\mysql\bin\mysqldump.exe" -u root --single-transaction --routines --triggers dole_payroll > dole_payroll.sql

if %ERRORLEVEL% neq 0 (
    echo [ERROR] Failed to export from XAMPP MySQL!
    pause
    exit /b 1
)

echo [SUCCESS] Database exported to dole_payroll.sql

echo [2/3] Uploading to Google Drive...
rclone.exe mkdir gdrive:DOLE_Payroll_Database
rclone.exe copy dole_payroll.sql gdrive:DOLE_Payroll_Database/

if %ERRORLEVEL% neq 0 (
    echo [ERROR] Failed to upload to Google Drive!
    pause
    exit /b 1
)

echo [SUCCESS] Database uploaded to Google Drive

echo [3/3] Verification complete...
for %%F in ("dole_payroll.sql") do (
    echo File size: %%~zF bytes
    echo Modified: %%~tF
)

echo.
echo ================================================
echo  Automated Push Completed Successfully!
echo ================================================
echo.
echo Your database has been:
echo 1. Exported from XAMPP MySQL
echo 2. Saved to project SQL file  
echo 3. Uploaded to Google Drive
echo.
pause
