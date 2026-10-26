@echo off
title IndoPOS - 1-Click Smart Launcher
color F0
setlocal enabledelayedexpansion

REM =======================================================================
REM INDOPOS 1-CLICK INSTANT LAUNCHER
REM Otomatis: Cek/Pasang Python -> Cek/Pasang Library -> Migrasi -> Start Server -> Buka Browser
REM =======================================================================

cls
echo =======================================================================
echo                 INDOPOS - 1-CLICK INSTANT LAUNCHER v3.0               
echo =======================================================================
echo.

REM -----------------------------------------------------------------------
REM LANGKAH 1: DETEKSI ATAU PASANG PYTHON OTOMATIS
REM -----------------------------------------------------------------------
echo [1/4] Memeriksa Python di komputer/laptop ini...
call :DETECT_PYTHON

if not defined PY_CMD (
    echo.
    echo [!] Python belum ditemukan. Memulai pemasangan Python otomatis...
    call :AUTO_INSTALL_PYTHON
    call :DETECT_PYTHON
)

if not defined PY_CMD (
    echo.
    echo [ERROR] Gagal mendeteksi Python setelah instalasi.
    echo Silakan restart Command Prompt atau pasang Python manual dari python.org.
    echo.
    pause
    exit /b 1
)

for /f "tokens=*" %%v in ('%PY_CMD% --version 2^>^&1') do set PY_VER=%%v
echo [OK] %PY_VER% siap digunakan (%PY_CMD%).

REM -----------------------------------------------------------------------
REM LANGKAH 2: DETEKSI & PASANG DEPENDENSI (LIBRARY)
REM -----------------------------------------------------------------------
echo.
echo [2/4] Memeriksa pustaka Python (Django, DuckDB, Pandas, Requests, dll)...
%PY_CMD% -c "import django, duckdb, pandas, requests, openpyxl" >nul 2>nul
if %errorlevel% neq 0 (
    echo [INFO] Menemukan pustaka yang belum lengkap.
    echo [PROSES] Mengunduh dan memasang seluruh pustaka dari requirements.txt...
    echo.
    %PY_CMD% -m pip install --upgrade pip >nul 2>nul
    %PY_CMD% -m pip install -r requirements.txt
    if %errorlevel% neq 0 (
        echo.
        echo [ERROR] Gagal memasang beberapa pustaka Python.
        echo Pastikan komputer terhubung ke internet saat instalasi pertama kali.
        pause
        exit /b 1
    )
    echo [SUKSES] Seluruh pustaka Python berhasil dipasang!
) else (
    echo [OK] Seluruh pustaka Python lengkap dan siap.
)

REM -----------------------------------------------------------------------
REM LANGKAH 3: SETUP FOLDER, DATABASE & DUCKDB ANALYTICS
REM -----------------------------------------------------------------------
echo.
echo [3/4] Menyiapkan basis data & engine DuckDB...
if not exist "logs" mkdir logs
if not exist "storage\logs" mkdir storage\logs 2>nul

%PY_CMD% manage.py migrate --no-input >nul 2>nul
%PY_CMD% manage.py init_duckdb >nul 2>nul
echo [OK] Basis data produk (1071 item) & DuckDB Engine siap digunakan offline.

REM -----------------------------------------------------------------------
REM LANGKAH 4: JALANKAN SERVER & BUKA BROWSER OTOMATIS
REM -----------------------------------------------------------------------
echo.
echo [4/4] Meluncurkan server latar belakang & membuka kasir di browser...
start "IndoPOS Backend Server (127.0.0.1:8000)" %PY_CMD% manage.py runserver 127.0.0.1:8000

echo Menunggu server aktif (3 detik)...
timeout /t 3 >nul 2>nul

echo Membuka aplikasi di peramban (browser)...
start http://127.0.0.1:8000/
start http://127.0.0.1:8000/analytics/

:SERVER_RUNNING
cls
echo =======================================================================
echo          [SUKSES] SISTEM INDOPOS SEDANG AKTIF & BERJALAN              
echo =======================================================================
echo.
echo  Aplikasi dapat diakses di:
echo  1. Kasir Barcode POS:        http://127.0.0.1:8000/
echo  2. Dasbor Analitik DuckDB:   http://127.0.0.1:8000/analytics/
echo  3. Panel Admin Toko:         http://127.0.0.1:8000/admin/
echo     (Login: admin  ^|  Pass: 12345678)
echo.
echo =======================================================================
echo  MENU PENGENDALIAN SISTEM (Ketik angka jika ingin aksi lain):
echo =======================================================================
echo  [1] Buka Ulang Halaman Kasir POS di Browser
echo  [2] Buka Ulang Dasbor Analitik DuckDB di Browser
echo  [3] Uji Coba Virtual Barcode Scanner (Hardware Test)
echo  [4] Jalankan Dasbor Filament Admin (Mode PHP Laravel)
echo  [0] Tutup dan Hentikan Server
echo =======================================================================
set "AKSI="
set /p AKSI="Pilihan Anda [Tekan ENTER untuk biarkan tetap berjalan]: "

if "%AKSI%"=="1" (
    start http://127.0.0.1:8000/
    goto SERVER_RUNNING
)
if "%AKSI%"=="2" (
    start http://127.0.0.1:8000/analytics/
    goto SERVER_RUNNING
)
if "%AKSI%"=="3" (
    cls
    echo =======================================================================
    echo       UJI COBA OTOMATIS SCANNER BARCODE (VIRTUAL TEST SUITE)
    echo =======================================================================
    %PY_CMD% run_scan_test.py
    pause
    goto SERVER_RUNNING
)
if "%AKSI%"=="4" (
    call :LAUNCH_LARAVEL
    goto SERVER_RUNNING
)
if "%AKSI%"=="0" (
    goto STOP_SERVER
)

goto SERVER_RUNNING


REM =======================================================================
REM SUBROUTINE: DETEKSI PYTHON MANDIRI
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
REM SUBROUTINE: PEMASANGAN PYTHON OTOMATIS (SILENT 1-KLIK)
REM =======================================================================
:AUTO_INSTALL_PYTHON
echo =======================================================================
echo          MENGUNDUH & MEMASANG PYTHON 3.11 OTOMATIS (SILENT)            
echo =======================================================================
echo Mohon tunggu sejenak, proses ini berjalan otomatis 1 kali saja...
echo.

set "PY_INSTALLER=%TEMP%\python_installer_311.exe"

where curl >nul 2>nul
if %errorlevel% equ 0 (
    echo [PROSES] Mengunduh Python resmi via curl...
    curl -# -L -o "%PY_INSTALLER%" https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe
) else (
    echo [PROSES] Mengunduh Python resmi via PowerShell...
    powershell -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object System.Net.WebClient).DownloadFile('https://www.python.org/ftp/python/3.11.9/python-3.11.9-amd64.exe', '%PY_INSTALLER%')"
)

if not exist "%PY_INSTALLER%" (
    echo [ERROR] Gagal mengunduh installer Python. Pastikan terhubung internet.
    goto :eof
)

echo [PROSES] Memasang Python secara otomatis ke sistem...
start /wait "" "%PY_INSTALLER%" /quiet InstallAllUsers=0 PrependPath=1 Include_pip=1 Include_test=0
del /f /q "%PY_INSTALLER%" >nul 2>nul

REM Suntikkan path Python ke sesi CMD saat ini
set "PATH=%LOCALAPPDATA%\Programs\Python\Python311;%LOCALAPPDATA%\Programs\Python\Python311\Scripts;%PATH%"
if exist "%LOCALAPPDATA%\Programs\Python\Python311\python.exe" (
    set "PY_CMD=%LOCALAPPDATA%\Programs\Python\Python311\python.exe"
)
goto :eof


REM =======================================================================
REM SUBROUTINE: DETEKSI & JALANKAN LARAVEL FILAMENT
REM =======================================================================
:LAUNCH_LARAVEL
set "PHP_BIN="
php -v >nul 2>nul
if %errorlevel% equ 0 set "PHP_BIN=php"

if not defined PHP_BIN (
    for %%X in (
        "C:\xampp\php\php.exe"
        "D:\xampp\php\php.exe"
        "C:\laragon\bin\php\php-8.2*\php.exe"
        "C:\php\php.exe"
    ) do (
        if exist %%X set "PHP_BIN=%%~X"
    )
)

if not defined PHP_BIN (
    echo [PERINGATAN] PHP/XAMPP tidak ditemukan di komputer ini.
    pause
    goto :eof
)

echo Menyiapkan Laravel di port 8080 (agar tidak bentrok dengan Kasir POS port 8000)...
"%PHP_BIN%" artisan optimize:clear >nul 2>nul
start "IndoPOS Laravel Server (127.0.0.1:8080)" "%PHP_BIN%" artisan serve --port=8080
timeout /t 3 >nul 2>nul
start http://127.0.0.1:8080/admin
goto :eof


REM =======================================================================
REM SUBROUTINE: HENTIKAN SERVER
REM =======================================================================
:STOP_SERVER
echo.
echo Menghentikan server IndoPOS...
taskkill /F /FI "WINDOWTITLE eq IndoPOS*" >nul 2>nul
taskkill /F /IM python.exe /FI "MODULES eq *django*" >nul 2>nul
echo Terima kasih telah menggunakan IndoPOS.
timeout /t 2 >nul 2>nul
exit /b 0
