@echo off
:: ================================================
:: DOLE Payroll - Get rclone for Google Drive
:: ================================================

echo Downloading rclone for Google Drive integration...

echo.
echo This will download rclone.exe to your project root.
echo rclone is required for Google Drive upload/download functionality.
echo.

:: Check if rclone already exists
if exist "rclone.exe" (
    echo rclone.exe already exists in project root!
    echo Skipping download.
    pause
    exit /b 0
)

echo Downloading rclone v1.73.4 (Windows 64-bit)...
powershell -Command "Invoke-WebRequest -Uri 'https://downloads.rclone.org/v1.73.4/rclone-v1.73.4-windows-amd64.zip' -OutFile 'rclone.zip'"

echo Extracting rclone...
powershell -Command "Expand-Archive -Path 'rclone.zip' -DestinationPath '.' -Force"

:: Move rclone.exe to project root
echo Setting up rclone...
move "rclone-v1.73.4-windows-amd64\rclone.exe" "rclone.exe" >nul 2>&1

:: Clean up
rmdir /s /q "rclone-v1.73.4-windows-amd64" 2>nul
del rclone.zip 2>nul

if exist "rclone.exe" (
    echo.
    echo SUCCESS: rclone.exe downloaded and ready!
    echo.
    echo Next step: Configure rclone with your Google account:
    echo   .\rclone.exe config
    echo.
) else (
    echo ERROR: Failed to download rclone.exe
    echo Please download manually from: https://rclone.org/downloads/
)

pause
