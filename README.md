## Project Test Nilai Siswa (Laravel + Inertia + Vue)

Manajemen data Nilai dan Siswa dengan pencarian, filter, pagination, impor/ekspor Excel, dan validasi unik (nama + kelas) yang tidak peka huruf besar/kecil.

### Arsitektur
Pendekatan: Laravel + Inertia.js + Vue 3 dalam satu repositori.
Alasan memilih model digabung:
1. Validasi sekali di backend langsung dipakai di form (konsisten).
2. Kecepatan pengembangan: fokus ke fitur, bukan wiring API.
3. Routing dan state auth simpel (session Laravel langsung jalan).
4. Masih bisa diekstrak ke API murni kalau nantinya butuh klien lain.

### Fitur Utama
- CRUD Siswa (unik: kombinasi nama + kelas, case insensitive)
- CRUD Nilai dengan badge kategori nilai
- Pencarian & filter (autocomplete siswa dengan debounce)
- Pagination
- Impor & ekspor Excel
- Autocomplete Siswa saat input Nilai
- Login + ubah kata sandi
- Modal reusable dengan reset state bersih

### Prasyarat
- PHP 8.3+
- Composer
- Node.js 20+
- npm
- atau PostgreSQL

### Instalasi
```bash
git clone https://github.com/Sulthannn/projecttest_nilaisiswa.git
cd projecttest_nilaisiswa
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
php artisan serve
npm run dev
```
Akses: http://localhost:8000

### Akun Demo
Email: `test@gmail.com`
Password: `admin123`
(Dibuat oleh seeder.)

### Skrip npm
- `npm run dev` : Jalankan Vite dev server
- `npm run build` : Build produksi

### Build Produksi (opsional)
```bash
npm run build
```
File hasil akan berada di `public/build`
