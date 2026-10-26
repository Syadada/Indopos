@echo off
title IndoPOS - Update Sistem Otomatis
color 0B

echo ========================================================
echo       INDOPOS - AUTO UPDATE SYSTEM DARI GITHUB           
echo ========================================================
echo.

echo [1/3] Mengamankan perubahan lokal...
git update-index --assume-unchanged database/database.sqlite >nul 2>&1
git stash >nul 2>&1

echo [2/3] Mengambil update program terbaru dari server GitHub...
git pull origin main

echo [3/3] Memastikan file database lokal tetap aman...
git update-index --assume-unchanged database/database.sqlite >nul 2>&1

echo.
echo ========================================================
echo   [SUKSES] Program IndoPOS berhasil diperbarui!
echo   Silakan jalankan JALANKAN_SISTEM.bat untuk mulai.
echo ========================================================
echo.
pause
