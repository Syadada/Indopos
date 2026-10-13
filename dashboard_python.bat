@echo off
title IndoPOS - Python & DuckDB Server Launcher
color 0B

echo ========================================================
echo       INDOPOS - PYTHON & DUCKDB SERVER LAUNCHER         
echo ========================================================
echo.

REM 1. Check Python Executable
where python >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Python tidak ditemukan di sistem PATH!
    echo Silakan pastikan Python 3.10+ terpasang di komputer ini.
    echo Mengarahkan ke halaman download Python...
    start https://www.python.org/downloads/
    pause
    exit /b 1
)

for /f "tokens=*" %%v in ('python --version 2^>^&1') do echo [OK] %%v terdeteksi.

REM 2. Check and Auto-Install Requirements
echo.
echo [CHECK] Memeriksa pustaka Python...
python -c "import django, duckdb, psycopg2, requests, pandas" >nul 2>nul
if %errorlevel% neq 0 (
    echo [SETUP] Memasang pustaka yang dibutuhkan dari requirements.txt...
    python -m pip install -r requirements.txt
) else (
    echo [OK] Seluruh pustaka Python lengkap.
)

REM 3. Check & Run Database Migrations
echo [SETUP] Memeriksa migrasi database Django...
python manage.py migrate --no-input >nul 2>nul

REM 4. Inisialisasi DuckDB & Katalog Sembako Offline
echo [SETUP] Menginisialisasi DuckDB Engine & Katalog Sembako Offline...
python manage.py init_duckdb

REM 5. Ensure Logs Directory Exists
if not exist "logs" mkdir logs

REM 6. Launch Browser & Start Server
echo.
echo ========================================================
echo    Server Kasir POS Aktif di: http://127.0.0.1:8000/
echo    Panel Django Admin di:     http://127.0.0.1:8000/admin/
echo    Dasbor Analisis DuckDB di: http://127.0.0.1:8000/analytics/
echo    Akun Admin: Username [admin]  Password [12345678]
echo ========================================================
echo.
echo Membuka browser otomatis ke Kasir POS...
start http://127.0.0.1:8000/

echo Memulai Server Django Development (Offline Mode)...
python manage.py runserver 127.0.0.1:8000

pause
