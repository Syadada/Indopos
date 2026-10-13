@echo off
title IndoPOS - Barcode Scanner Test Suite
color 0A

echo =======================================================================
echo         INDOPOS - UJI COBA OTOMATIS SCANNER BARCODE (VIRTUAL)           
echo =======================================================================
echo.
echo Menjalankan simulasi perangkat keras barcode scanner secara otomatis...
echo (Menguji pembacaan barcode, multi-qty keranjang, katalog sembako, dan checkout)
echo.

python run_scan_test.py

echo.
pause
