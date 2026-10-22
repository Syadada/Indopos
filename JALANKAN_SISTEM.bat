@echo off
title IndoPOS - All-in-One Smart System Launcher
color 0B

:MENU
cls
echo =======================================================================
echo          INDOPOS - ALL-IN-ONE SMART SYSTEM LAUNCHER v2.2              
echo =======================================================================
echo.
echo  PILIH SISTEM YANG INGIN DIJALANKAN:
echo.
echo  [1] Kasir POS & DuckDB Analytics (Python Django - Rekomendasi Utama)
echo  [2] Dasbor Filament Admin (PHP Laravel)
echo  [3] Uji Coba Virtual Barcode Scanner (Test Suite Hardware POS)
echo  [4] Pasang Python Otomatis (Langsung 1-Klik tanpa buka web)
echo  [5] Buka Situs Resmi Python di Browser (Manual)
echo  [0] Keluar
echo.
echo =======================================================================
set "PILIHAN="
set /p PILIHAN="Ketik angka pilihan (1/2/3/4/5/0) lalu tekan ENTER: "

if "%PILIHAN%"=="1" goto MODE_PYTHON
if "%PILIHAN%"=="2" goto MODE_LARAVEL
if "%PILIHAN%"=="3" goto MODE_SCAN_TEST
if "%PILIHAN%"=="4" goto AUTO_INSTALL_PYTHON
if "%PILIHAN%"=="5" goto DOWNLOAD_PYTHON
if "%PILIHAN%"=="0" goto KELUAR

echo.
echo [!] Pilihan "%PILIHAN%" tidak valid.
pause
goto MENU


REM =======================================================================
REM DETEKSI PYTHON MANDIRI
REM =======================================================================
:DETECT_PYTHON
set "PY_CMD="

REM 1. Cek python di PATH
python --version >nul 2>nul
if %errorlevel% equ 0 (
    set "PY_CMD=python"
    goto :eof
)

REM 2. Cek Python Launcher resmi Windows (py.exe)
py -3 --version >nul 2>nul
if %errorlevel% equ 0 (
    set "PY_CMD=py -3"
    goto :eof
)

REM 3. Cek direktori instalasi standar Python di Windows
for %%P in (
    "%LOCALAPPDATA%\Programs\Python\Python313\python.exe"
    "%LOCALAPPDATA%\Programs\Python\Python312\python.exe"
    "%LOCALAPPDATA%\Programs\Python\Python311\python.exe"
    "%LOCALAPPDATA%\Programs\Python\Python310\python.exe"
    "C:\Python313\python.exe"
    "C:\Python312\python.exe"
    "C:\Python311\python.exe"
    "C:\Python310\python.exe"
    "C:\Program Files\Python313\python.exe"
    "C:\Program Files\Python312\python.exe"
    "C:\Program Files\Python311\python.exe"
    "C:\Program Files\Python310\python.exe"
) do (
    if exist %%P (
        set "PY_CMD=%%~P"
        goto :eof
    )
)
goto :eof


REM =======================================================================
REM MENU 1: MODE PYTHON (KASIR POS & DUCKDB)
REM =======================================================================
:MODE_PYTHON
cls
echo =======================================================================
echo     MEMULAI SERVER KASIR POS & ANALITIK DUCKDB (OFFLINE MODE)
echo =======================================================================
echo.
echo [1/4] Memeriksa Python di laptop ini...
call :DETECT_PYTHON

if not defined PY_CMD (
    echo.
    echo -------------------------------------------------------------------
    echo [PERINGATAN] Python belum terpasang di laptop ini!
    echo -------------------------------------------------------------------
    echo Fitur Kasir Barcode & DuckDB Analytics membutuhkan Python 3.10+.
    echo.
    echo Pilihan Pemasangan:
    echo  [1] Pasang Otomatis Sekarang (Download & install langsung tanpa buka web)
    echo  [2] Buka Situs Resmi Python di Browser (Manual)
    echo  [3] Kembali ke Menu Utama
    echo.
    set "PY_OPT="
    set /p PY_OPT="Pilih opsi (1/2/3): "
    if "%PY_OPT%"=="1" goto AUTO_INSTALL_PYTHON
    if "%PY_OPT%"=="2" goto DOWNLOAD_PYTHON
    goto MENU
)

for /f "tokens=*" %%v in ('%PY_CMD% --version 2^>^&1') do set PY_VER=%%v
echo [OK] %PY_VER% terdeteksi.

echo.
echo [2/4] Memeriksa pustaka dependensi (requirements.txt)...
%PY_CMD% -c "import django, duckdb, psycopg2, requests, pandas" >nul 2>nul
if %errorlevel% neq 0 (
    echo [INFO] Terdeteksi pustaka yang belum lengkap di laptop ini.
    echo [PROSES] Memasang pustaka otomatis dari requirements.txt...
    echo.
    %PY_CMD% -m pip install -r requirements.txt
    if %errorlevel% neq 0 (
        echo.
        echo [ERROR] Gagal memasang beberapa pustaka Python.
        echo Pastikan laptop terhubung ke internet saat instalasi awal.
        pause
        goto MENU
    )
    echo [SUKSES] Seluruh pustaka berhasil dipasang!
) else (
    echo [OK] Seluruh pustaka Python lengkap.
)

echo.
echo [3/4] Menyiapkan folder audit log & inisialisasi basis data DuckDB...
if not exist "logs" mkdir logs
if not exist "storage\logs" mkdir storage\logs 2>nul

%PY_CMD% manage.py migrate --no-input >nul 2>nul
%PY_CMD% manage.py init_duckdb >nul 2>nul
echo [OK] Basis data & DuckDB Engine siap digunakan offline.

echo.
echo [4/4] Meluncurkan server...
echo =======================================================================
echo  SISTEM INDOPOS 100%% OFFLINE AKTIF
echo  - Kasir Barcode POS:        http://127.0.0.1:8000/
echo  - Dasbor Analitik DuckDB:   http://127.0.0.1:8000/analytics/
echo  - Panel Manajemen Admin:    http://127.0.0.1:8000/admin/
echo  - Kredensial Admin:         User: admin  ^|  Pass: 12345678
echo =======================================================================
echo.
echo Membuka browser otomatis...
start http://127.0.0.1:8000/

echo Memulai Server Django Development (Tekan Ctrl+C untuk berhenti)...
%PY_CMD% manage.py runserver 127.0.0.1:8000
echo.
echo Server telah dihentikan.
pause
goto MENU


REM =======================================================================
REM MENU 4: PEMASANGAN PYTHON OTOMATIS (DIRECT SILENT INSTALL)
REM =======================================================================
:AUTO_INSTALL_PYTHON
cls
echo =======================================================================
echo      PEMASANGAN PYTHON OTOMATIS (DIRECT 1-KLIK TANPA BUKA WEB)
echo =======================================================================
echo.
echo [1/3] Menyiapkan installer resmi Python 3.11 64-bit...
set "PY_INSTALLER=%TEMP%\python_installer_311.exe"

REM Gunakan curl jika tersedia, atau powershell sebagai fallback
where curl >nul 2>nul
if %errorlevel% equ 0 (
    echo [PROSES] Mengunduh Python langsung dari python.org via curl...
    curl -# -L -o "%PY_INSTALLER%" https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe
) else (
    echo [PROSES] Mengunduh Python langsung via PowerShell...
    powershell -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object System.Net.WebClient).DownloadFile('https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe', '%PY_INSTALLER%')"
)

if not exist "%PY_INSTALLER%" (
    echo.
    echo [ERROR] Gagal mengunduh installer Python!
    echo Pastikan laptop terhubung ke internet.
    pause
    goto MENU
)

echo.
echo [2/3] Memasang Python secara otomatis (Silent Install)...
echo (Mohon tunggu sebentar, sistem sedang memasang Python dan konfigurasi PATH...)
start /wait "" "%PY_INSTALLER%" /quiet InstallAllUsers=0 PrependPath=1 Include_pip=1 Include_test=0

REM Bersihkan file installer sementara
del /f /q "%PY_INSTALLER%" >nul 2>nul

echo.
echo [3/3] Memeriksa hasil instalasi...
call :DETECT_PYTHON

if defined PY_CMD (
    echo.
    echo =======================================================================
    echo [SUKSES] Python berhasil dipasang dan siap digunakan!
    echo =======================================================================
    echo Menjalankan sistem Kasir POS & Analitik DuckDB secara otomatis...
    timeout /t 3 >nul 2>nul
    goto MODE_PYTHON
) else (
    echo [INFO] Python telah selesai dipasang ke sistem.
    echo Silakan tutup jendela ini dan jalankan kembali JALANKAN_SISTEM.bat
    echo agar perubahan PATH sistem Windows terbaca sepenuhnya.
    echo.
    pause
    goto MENU
)


REM =======================================================================
REM MENU 2: MODE LARAVEL FILAMENT
REM =======================================================================
:MODE_LARAVEL
cls
echo =======================================================================
echo            MEMULAI SERVER DASBOR FILAMENT ADMIN (LARAVEL)
echo =======================================================================
echo.
echo [1/3] Memeriksa PHP / XAMPP di laptop ini...
set "PHP_BIN="

REM Cek php di PATH
php -v >nul 2>nul
if %errorlevel% equ 0 set "PHP_BIN=php"

REM Cek lokasi umum XAMPP atau Laragon jika belum ketemu
if not defined PHP_BIN (
    for %%X in (
        "C:\xampp\php\php.exe"
        "D:\xampp\php\php.exe"
        "E:\xampp\php\php.exe"
        "C:\laragon\bin\php\php-8.2*\php.exe"
        "C:\laragon\bin\php\php-8.1*\php.exe"
        "C:\laragon\bin\php\php-8.3*\php.exe"
        "C:\laragon\bin\php\php-8.0*\php.exe"
        "C:\php\php.exe"
    ) do (
        if exist %%X (
            set "PHP_BIN=%%~X"
        )
    )
)

if not defined PHP_BIN (
    echo.
    echo -------------------------------------------------------------------
    echo [PERINGATAN] PHP maupun XAMPP tidak ditemukan di laptop ini!
    echo -------------------------------------------------------------------
    echo Mode Laravel membutuhkan PHP 8.1+ atau XAMPP.
    echo Silakan install XAMPP dari https://www.apachefriends.org/
    echo atau gunakan [Opsi 1] Mode Python DuckDB yang bisa diinstall otomatis.
    echo.
    pause
    goto MENU
)

echo [OK] PHP terdeteksi: %PHP_BIN%

echo.
echo [2/3] Menyiapkan environment Laravel & migrasi database...
if not exist ".env" (
    echo [SETUP] Menyiapkan file konfigurasi .env...
    if exist ".env.example" copy .env.example .env >nul
    "%PHP_BIN%" artisan key:generate
)

if not exist "vendor" (
    echo [PERINGATAN] Folder vendor belum ada.
    where composer >nul 2>nul
    if %errorlevel% equ 0 (
        echo Menjalankan composer install...
        composer install --no-interaction --prefer-dist
    ) else (
        echo [INFO] Pastikan folder vendor telah disalin jika tidak ada composer.
    )
)

"%PHP_BIN%" artisan optimize:clear >nul 2>nul
"%PHP_BIN%" artisan migrate --force >nul 2>nul

echo.
echo [3/3] Meluncurkan Dasbor Admin Filament...
echo =======================================================================
echo    Dasbor Admin Aktif di: http://127.0.0.1:8000/admin
echo =======================================================================
echo Membuka browser...
start http://127.0.0.1:8000/admin

echo Menjalankan server Laravel (Tekan Ctrl+C untuk berhenti)...
"%PHP_BIN%" artisan serve --port=8000
echo.
echo Server Laravel telah dihentikan.
pause
goto MENU


REM =======================================================================
REM MENU 3: UJI COBA SCANNER BARCODE VIRTUAL
REM =======================================================================
:MODE_SCAN_TEST
cls
echo =======================================================================
echo       UJI COBA OTOMATIS SCANNER BARCODE (VIRTUAL TEST SUITE)
echo =======================================================================
echo.
call :DETECT_PYTHON
if not defined PY_CMD (
    echo [ERROR] Python dibutuhkan untuk menjalankan uji scanner barcode.
    pause
    goto MENU
)

echo Menjalankan simulasi scanner barcode dan verifikasi DuckDB...
echo.
%PY_CMD% run_scan_test.py
echo.
pause
goto MENU


REM =======================================================================
REM MENU 5: UNDUH PYTHON MANUAL LEWAT WEB
REM =======================================================================
:DOWNLOAD_PYTHON
cls
echo =======================================================================
echo              PANDUAN MENGUNDUH & MEMASANG PYTHON
echo =======================================================================
echo.
echo Membuka peramban ke situs resmi Python (https://www.python.org/downloads/)...
start https://www.python.org/downloads/
echo.
echo -------------------------------------------------------------------
echo LANGKAH PENTING SAAT MEMASANG PYTHON DI WINDOWS:
echo 1. Unduh installer Python (versi 3.10, 3.11, atau 3.12).
echo 2. Buka file installer (.exe).
echo 3. SANGAT PENTING: Pada tampilan paling bawah installer,
echo    CENTANG KOTAK: [X] "Add python.exe to PATH"
echo 4. Klik "Install Now" dan tunggu hingga selesai.
echo 5. Buka kembali file batch ini!
echo -------------------------------------------------------------------
echo.
pause
goto MENU


REM =======================================================================
REM MENU 0: KELUAR
REM =======================================================================
:KELUAR
cls
echo Terima kasih telah menggunakan IndoPOS.
timeout /t 2 >nul 2>nul
exit /b 0
