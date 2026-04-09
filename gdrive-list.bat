@echo off
:: ================================================
:: DOLE Payroll - List Google Drive Files
:: ================================================

echo Listing files in Google Drive...

:: Check if rclone exists
if not exist "rclone.exe" (
    echo ERROR: rclone.exe not found!
    echo Please run: .\setup-rclone.bat first
    pause
    exit /b 1
)

echo.
echo === Google Drive Root ===
rclone.exe lsd gdrive:

echo.
echo === DOLE_Payroll_Database Folder ===
rclone.exe ls gdrive:DOLE_Payroll_Database/

echo.
pause
