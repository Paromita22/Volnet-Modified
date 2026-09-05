@echo off
title VolNet Server
cd /d "%~dp0"

echo ==========================================================
echo               VolNet Platform Starter (Windows)
echo ==========================================================

:: Check if php command exists
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not found in your System PATH.
    echo Please install PHP (or XAMPP/WampServer) and add PHP to your PATH.
    echo.
    pause
    exit /b 1
)

:: Check if .env file exists
if not exist ".env" (
    if exist ".env.example" (
        echo [.env missing] Copying .env.example to .env ...
        copy .env.example .env >nul
    )
)

echo.
echo   🚀 Connecting to Supabase Database...
echo   🌐 VolNet Server is LIVE at: http://localhost:8000
echo   ⚡ Press Ctrl+C in this window to stop the server
echo ==========================================================
echo.

:: Launch default browser
start http://localhost:8000

:: Start PHP Built-in Server with essential extensions
php -d extension=pdo_pgsql -d extension=pgsql -d extension=mysqli -d extension=gd -d opcache.enable=0 -S 0.0.0.0:8000

pause
