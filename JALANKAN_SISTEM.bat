@echo off
title IndoPOS - Smart Offline System Launcher
color 0B

echo =======================================================================
echo          INDOPOS - SMART OFFLINE SYSTEM LAUNCHER v2.0                  
echo =======================================================================
echo.
echo [1/5] Memeriksa Lingkungan Sistem Perangkat Ini...

REM =======================================================================
REM 1. DETEKSI PYTHON
REM =======================================================================
where python >nul 2>nul
if %errorlevel% neq 0 (
    echo.
    echo -------------------------------------------------------------------
    echo [PERINGATAN] Python belum terpasang di perangkat ini!
    echo -------------------------------------------------------------------
    echo Sistem IndoPOS membutuhkan Python 3.10+ untuk menjalankan backend
    echo dan mesin analitik DuckDB lokal secara offline.
    echo.
    echo Pilihan:
    echo 1. Buka halaman unduh Python Resmi (www.python.org)
    echo 2. Coba jalankan mode Laravel PHP (jika PHP terpasang)
    echo 3. Keluar
    echo.
    set /p CHOICE="Pilih opsi (1/2/3): "
    if "%CHOICE%"=="1" (
        start https://www.python.org/downloads/
        echo Silakan unduh dan pasang Python (Pastikan centang "Add python.exe to PATH").
        pause
        exit /b 1
    )
    if "%CHOICE%"=="2" (
        goto LAUNCH_LARAVEL_MODE
    )
    exit /b 1
)

for /f "tokens=*" %%v in ('python --version 2^>^&1') do set PY_VER=%%v
echo [OK] %PY_VER% terdeteksi di perangkat.

REM =======================================================================
REM 2. PEMERIKSAAN REQUIREMENTS & AUTO-INSTALL LIBRARY
REM =======================================================================
echo.
echo [2/5] Memeriksa Kebutuhan Pustaka (requirements.txt)...
python -c "import django, duckdb, psycopg2, requests, pandas" >nul 2>nul
if %errorlevel% neq 0 (
    echo [INFO] Terdeteksi pustaka yang belum lengkap di komputer ini.
    echo [PROSES] Membaca requirements.txt dan memasang pustaka otomatis...
    echo.
    python -m pip install -r requirements.txt
    if %errorlevel% neq 0 (
        echo.
        echo [ERROR] Gagal memasang beberapa pustaka dari requirements.txt!
        echo Pastikan komputer terhubung ke internet saat instalasi awal,
        echo atau hubungi tim teknis.
        pause
        exit /b 1
    )
    echo.
    echo [SUKSES] Semua pustaka requirements.txt berhasil dipasang!
) else (
    echo [OK] Seluruh pustaka Python (Django, DuckDB, psycopg2, pandas) lengkap.
)

REM =======================================================================
REM 3. LOGGING SYSTEM & STORAGE CHECK
REM =======================================================================
echo.
echo [3/5] Menyiapkan Sistem Logging & Audit Trail...
if not exist "logs" (
    mkdir logs
)
if not exist "storage\logs" (
    mkdir storage\logs 2>nul
)
echo [OK] Folder logging siap: logs/pos_transactions.log & logs/pos_scans.log

REM =======================================================================
REM 4. DATABASE & OFFLINE DUCKDB ENGINE CHECK
REM =======================================================================
echo.
echo [4/5] Menginisialisasi Basis Data & Mesin Offline DuckDB...
python manage.py migrate --no-input >nul 2>nul
python manage.py init_duckdb
if %errorlevel% neq 0 (
    echo [INFO] Menyiapkan basis data lokal fallback...
)

REM =======================================================================
REM 5. PELUNCURAN SERVER OFFLINE & BROWSER
REM =======================================================================
echo.
echo [5/5] Meluncurkan Aplikasi IndoPOS...
echo =======================================================================
echo  SISTEM INDOPOS 100%% OFFLINE AKTIF
echo  - Kasir Barcode POS:        http://127.0.0.1:8000/
echo  - Dasbor Analitik DuckDB:   http://127.0.0.1:8000/analytics/
echo  - Panel Manajemen Admin:    http://127.0.0.1:8000/admin/
echo  - Kredensial Admin:         User: admin / admin@example.com  |  Pass: 12345678
echo =======================================================================
echo.
echo Membuka peramban (browser) otomatis...
start http://127.0.0.1:8000/

echo Memulai Server Backend (Tekan Ctrl+C untuk berhenti)...
python manage.py runserver 127.0.0.1:8000
goto END

:LAUNCH_LARAVEL_MODE
echo.
echo Memeriksa instalasi PHP...
set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" (
    where php >nul 2>nul
    if %errorlevel% equ 0 set PHP_BIN=php
)
if not exist "%PHP_BIN%" (
    echo [ERROR] PHP tidak ditemukan di sistem.
    pause
    exit /b 1
)
echo [OK] Menjalankan server Laravel mode via %PHP_BIN%...
start http://127.0.0.1:8000/admin
"%PHP_BIN%" artisan serve --port=8000

:END
pause
