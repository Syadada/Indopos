@echo off
title IndoPOS - Laravel Filament POS Server Launcher
color 0A

echo ========================================================
echo          INDOPOS - AUTOMATIC SERVER LAUNCHER            
echo          (Laravel 12 + Filament v3 POS System)          
echo ========================================================
echo.

set PHP_EXE=C:\xampp\php\php.exe
set COMPOSER_PHAR=C:\xampp\php\composer.phar

REM 1. Check PHP Executable
if exist "%PHP_EXE%" (
    echo [OK] Menggunakan PHP dari XAMPP: %PHP_EXE%
    REM Auto-enable essential PHP extensions in XAMPP php.ini
    if exist "C:\xampp\php\php.ini" (
        powershell -NoProfile -Command "(Get-Content 'C:\xampp\php\php.ini') -replace ';extension=intl', 'extension=intl' -replace ';extension=zip', 'extension=zip' -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite' -replace ';extension=sqlite3', 'extension=sqlite3' -replace ';extension=fileinfo', 'extension=fileinfo' | Set-Content -Encoding ASCII 'C:\xampp\php\php.ini'" >nul 2>&1
    )
) else (
    where php >nul 2>&1
    if %errorlevel% equ 0 (
        set PHP_EXE=php
        echo [OK] Menggunakan PHP dari System PATH
    ) else (
        echo [ERROR] PHP executable tidak ditemukan di %PHP_EXE% maupun System PATH!
        echo Pastikan XAMPP terinstall di C:\xampp atau PHP terdaftar di PATH.
        pause
        exit /b 1
    )
)

REM 2. Check & Install Dependencies (Fresh Installation Trigger)
if not exist "vendor\autoload.php" (
    echo [SETUP] Instalasi baru / belum lengkap terdeteksi! Menginstall dependensi Composer...
    if exist "%COMPOSER_PHAR%" (
        "%PHP_EXE%" "%COMPOSER_PHAR%" install --no-interaction --prefer-dist --ignore-platform-reqs
    ) else (
        composer install --no-interaction --prefer-dist --ignore-platform-reqs
    )
    echo [SETUP] Dependensi berhasil diinstall!
    echo.
)

REM 3. Check Environment File (.env)
if not exist ".env" (
    echo [SETUP] Membuat file .env dari .env.example...
    copy .env.example .env
    "%PHP_EXE%" artisan key:generate
    echo [SETUP] Konfigurasi environment selesai!
    echo.
)

REM 4. Ensure Essential Storage Directories
if not exist "storage\logs" mkdir "storage\logs"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "bootstrap\cache" mkdir "bootstrap\cache"

REM 5. Check Database Migrations
echo [SETUP] Menjalankan migrasi database...
"%PHP_EXE%" artisan migrate --force

REM 5. Clear Caches
echo [SETUP] Membersihkan cache aplikasi...
"%PHP_EXE%" artisan optimize:clear
echo.

REM 6. Launch Browser & Start Artisan Serve
echo ========================================================
echo    Server IndoPOS: http://127.0.0.1:8000/admin
echo    Login Default : admin@example.com / 12345678
echo ========================================================
echo Membuka browser ke Admin / Kasir POS...
start http://127.0.0.1:8000/admin

echo Menjalankan PHP Artisan Serve di port 8000...
"%PHP_EXE%" artisan serve --port=8000

pause
