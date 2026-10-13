@echo off
title IndoPOS - Dashboard Server Launcher
color 0A

echo ========================================================
echo          INDOPOS - AUTOMATIC SERVER LAUNCHER            
echo ========================================================
echo.

set PHP_EXE=C:\xampp\php\php.exe
set COMPOSER_PHAR=C:\xampp\php\composer.phar

REM 1. Check PHP Executable
if not exist "%PHP_EXE%" (
    echo [ERROR] PHP executable not found at %PHP_EXE%!
    echo Please install XAMPP or set PHP path correctly.
    pause
    exit /b 1
)

REM 2. Check & Install Dependencies (Fresh Installation Trigger)
if not exist "vendor" (
    echo [SETUP] Fresh installation detected! Installing composer dependencies...
    if exist "%COMPOSER_PHAR%" (
        "%PHP_EXE%" "%COMPOSER_PHAR%" install --no-interaction --prefer-dist
    ) else (
        composer install --no-interaction --prefer-dist
    )
    echo [SETUP] Dependencies installed successfully!
    echo.
)

REM 3. Check Environment File (.env)
if not exist ".env" (
    echo [SETUP] Creating .env file from .env.example...
    copy .env.example .env
    "%PHP_EXE%" artisan key:generate
    echo [SETUP] Environment configured!
    echo.
)

REM 4. Check Database Migrations
echo [SETUP] Running database migrations...
"%PHP_EXE%" artisan migrate --force

REM 5. Clear Caches
echo [SETUP] Clearing application cache...
"%PHP_EXE%" artisan optimize:clear
echo.

REM 6. Launch Browser & Start Artisan Serve
echo ========================================================
echo    Server running at: http://127.0.0.1:8000/admin
echo ========================================================
echo Opening browser...
start http://127.0.0.1:8000/admin

echo Starting PHP Artisan Serve...
"%PHP_EXE%" artisan serve --port=8000

pause
