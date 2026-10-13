# Persyaratan Sistem (System Requirements) - IndoPOS

Dokumen ini menjelaskan seluruh kebutuhan lingkungan sistem dan pustaka (*libraries*) untuk menjalankan aplikasi IndoPOS secara **100% Offline**.

---

## 💻 1. Kebutuhan Perangkat Keras & OS
- **Sistem Operasi:** Windows 10 / 11 (64-bit), Linux, atau macOS.
- **RAM:** Minimal 4 GB (Disarankan 8 GB).
- **Ruang Disk:** Minimal 1 GB ruang kosong.
- **Printer Kasir (Opsional):** Printer thermal 58mm atau 80mm (USB / Bluetooth / LAN).
- **Scanner (Opsional):** Barcode scanner USB / Wireless (Mode Keyboard Wedge).

---

## 🛠️ 2. Kebutuhan Runtime & Perangkat Lunak
Untuk menjalankan sistem, salah satu atau kedua runtime berikut diperlukan:

### A. Python Backend (Rekomendasi Utama)
- **Python:** Versi `3.10` atau `3.11+`
- **Pustaka Python:** Didaftarkan lengkap di [`requirements.txt`](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/laravel-filament-app/requirements.txt):
  - `django`: Framework Web & ORM Transaksional
  - `duckdb`: Mesin analitik kolumnar berkecepatan tinggi (offline)
  - `psycopg2-binary`: Driver PostgreSQL
  - `pandas` & `openpyxl`: Pemroses data Excel & CSV Sembako
  - `requests`: Auto-fetch barcode dengan fallback offline aman

### B. PHP / Laravel Backend (Alternatif)
- **PHP:** Versi `8.2` atau `8.3` (dengan ekstensi: `pdo_pgsql`, `pdo_sqlite`, `curl`, `mbstring`, `fileinfo`)
- **Composer:** Versi 2.x

---

## 🗄️ 3. Basis Data (Database)
Sistem dirancang untuk beroperasi secara **100% Offline**:
1. **PostgreSQL Lokal (Port 5432):**
   - Database: `indopos`
   - User: `rasyaad`
2. **DuckDB Engine Lokal:**
   - File: `indopos.duckdb` (Otomatis dibuat di folder aplikasi)
   - Memuat 1.000+ data sembako offline tanpa perlu internet.
3. **SQLite Lokal (Fallback Darurat):**
   - File: `db.sqlite3`

---

## 📁 4. Sistem Logging Terpadu (Audit Trail)
Semua aktivitas tercatat otomatis di direktori file lokal:
- **`logs/pos_transactions.log` / `storage/logs/pos_transactions.log`**: Mencatat setiap invoice penjualan, total omzet, metode pembayaran, kasir, dan nama pelanggan.
- **`logs/pos_scans.log` / `storage/logs/pos_scans.log`**: Mencatat setiap aktivitas scan barcode (status ditemukan, master katalog, atau barcode baru).
- **`logs/system_errors.log` / `storage/logs/system_errors.log`**: Mencatat kegagalan sistem dan trace error secara rapi.
