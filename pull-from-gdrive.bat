@echo off
:: ================================================
:: DOLE Payroll - Automated Pull from Google Drive
:: Google Drive -> Project -> XAMPP
:: ================================================

echo.
echo ================================================
echo  Automated Pull: Google Drive -> Project -> XAMPP
echo ================================================
echo.

echo [1/3] Downloading from Google Drive to project...
rclone.exe copy gdrive:DOLE_Payroll_Database/dole_payroll.sql .

if %ERRORLEVEL% neq 0 (
    echo [ERROR] Failed to download from Google Drive!
    pause
    exit /b 1
)

echo [SUCCESS] Database downloaded from Google Drive

for %%F in ("dole_payroll.sql") do (
    echo File size: %%~zF bytes
    echo Modified: %%~tF
)

echo [2/3] Importing to XAMPP MySQL...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS dole_payroll;"
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE dole_payroll;"
"C:\xampp\mysql\bin\mysql.exe" -u root dole_payroll < dole_payroll.sql

if %ERRORLEVEL% neq 0 (
    echo [ERROR] Failed to import to XAMPP MySQL!
    pause
    exit /b 1
)

echo [SUCCESS] Database imported to XAMPP MySQL

echo [3/3] Verification complete...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT COUNT(*) as employee_count FROM dole_payroll.employees;" 2>nul
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT COUNT(*) as payroll_count FROM dole_payroll.payroll_batches;" 2>nul

echo.
echo ================================================
echo  Automated Pull Completed Successfully!
echo ================================================
echo.
echo Your database has been:
echo 1. Downloaded from Google Drive
echo 2. Saved to project SQL file
echo 3. Imported to XAMPP MySQL
echo.
pause
