@echo off
:: ================================================
:: DOLE Payroll - Download from Google Drive
:: ================================================

echo Downloading database from Google Drive...

:: Check if rclone exists
if not exist "rclone.exe" (
    echo ERROR: rclone.exe not found!
    echo Please run: .\setup-rclone.bat first
    pause
    exit /b 1
)

:: Download from Google Drive
echo Downloading dole_payroll.sql from Google Drive...
rclone.exe copy gdrive:DOLE_Payroll_Database/dole_payroll.sql .

if %ERRORLEVEL%==0 (
    echo SUCCESS: Database downloaded from Google Drive!
    
    :: Show file info
    for %%F in ("dole_payroll.sql") do (
        echo Size: %%~zF bytes
        echo Modified: %%~tF
    )
) else (
    echo ERROR: Download failed!
)

pause
