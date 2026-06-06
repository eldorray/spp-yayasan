# SPP Yayasan — Manajemen Pembayaran SPP

Aplikasi Laravel untuk mengelola pembayaran SPP (Sumbangan Pembinaan Pendidikan) siswa. Multi-yayasan, multi-tahun ajaran, dengan laporan otomatis.

---

## Fitur

- **Manajemen siswa** — data induk, kelas, status aktif/non-aktif
- **Pembayaran SPP** — catat pembayaran per bulan, cicilan, tunggakan
- **Laporan** — rekap per kelas, per siswa, per periode
- **Multi user** — admin, bendahara, wali kelas
- **Export** — PDF / Excel

## Tech Stack

- **Backend:** Laravel 12
- **Frontend:** Vue 3 + Inertia
- **UI:** Tailwind CSS

## Setup

```bash
composer install
npm install
cp .env.example .env
# isi database
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```
