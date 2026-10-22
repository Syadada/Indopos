# IndoPOS - Python & DuckDB Retail Management System

Aplikasi POS (Kasir Barcode Retail) dan Manajemen Inventaris terintegrasi yang dimigrasikan ke backend **Python (Django)** dan didukung oleh mesin analitik **DuckDB Lokal**.

---

## 🚀 Cara Menjalankan Aplikasi

### Cara 1: Menggunakan File Batch Otomatis (Rekomendasi di Windows)
Cukup klik ganda (double-click) file:
```cmd
JALANKAN_SISTEM.bat
```
Skrip ini akan secara otomatis:
1. Memeriksa ketersediaan lingkungan Python.
2. Menjalankan migrasi database Django.
3. Menginisialisasi basis data analitik DuckDB lokal (`indopos.duckdb`) dan memuat katalog sembako.
4. Membuka peramban (browser) otomatis ke aplikasi Kasir POS di `http://127.0.0.1:8000/`.
5. Menjalankan server Django backend.

---

### Cara 2: Menjalankan Secara Manual via Terminal
1. Pasang dependensi (jika belum):
   ```cmd
   python -m pip install -r requirements.txt
   ```
2. Jalankan migrasi:
   ```cmd
   python manage.py migrate
   ```
3. Inisialisasi DuckDB:
   ```cmd
   python manage.py init_duckdb
   ```
4. Jalankan server:
   ```cmd
   python manage.py runserver 127.0.0.1:8000
   ```

---

## 🌐 Akses Halaman & Rute Aplikasi

| Halaman | URL | Keterangan |
| :--- | :--- | :--- |
| **Kasir Barcode POS** | `http://127.0.0.1:8000/` | Antarmuka kasir modern dengan pemindai barcode, keranjang belanja dinamis, pintasan uang pas, hitung kembalian, dan cetak struk thermal 58mm/80mm. |
| **Dasbor Analitik DuckDB** | `http://127.0.0.1:8000/analytics/` | Agregasi data penjualan berkecepatan tinggi bertenaga DuckDB (omzet, laba bersih, produk terlaris, metode pembayaran). |
| **Django Admin Panel** | `http://127.0.0.1:8000/admin/` | Panel admin bergaya Filament untuk manajemen Cabang, Kategori, Satuan, Produk, Multi-satuan, Stok Toko, dan Riwayat Transaksi. |
| **Cetak Struk Thermal** | `http://127.0.0.1:8000/transactions/<id>/print/` | Templat struk thermal siap cetak dengan dialog print otomatis. |

### 🔑 Akun Superuser Default (Django Admin)
- **Username:** `admin`
- **Password:** `admin123`

---

## ⚡ Fitur Utama Backend Python & DuckDB

1. **Mesin Analisis Kolumnar DuckDB (`indopos.duckdb`)**:
   - Memproses jutaan baris transaksi dan agregasi analitik penjualan secara instan (*sub-millisecond latency*).
   - Memuat dan mengindeks lebih dari 1.000 data katalog sembako dari `data_sembako.csv` dan `data_sembako_lengkap.csv`.
2. **Pencarian Barcode Bertingkat (*Hierarchical Barcode Lookup*)**:
   - **Tingkat 1:** Cek katalog produk aktif toko di database.
   - **Tingkat 2:** Cek katalog master sembako lokal via DuckDB.
   - **Tingkat 3:** Cek cache barcode lokal toko.
   - **Tingkat 4:** Auto-fetch online dari Cloud Open Food Facts API dengan caching otomatis.
3. **ORM Transaksional Django**:
   - Terhubung langsung dengan database PostgreSQL `indopos` yang sudah memiliki 1.071 produk aktif dan data transaksi.
   - Mendukung fallback ke database SQLite lokal jika diperlukan.
