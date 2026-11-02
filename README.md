# UKS Sekolah

Sistem Informasi **Unit Kesehatan Sekolah (UKS)** untuk sekolah TK, SD, dan SMP. Petugas UKS dapat mencatat kunjungan siswa/guru ke UKS, mengisi raport kesehatan (MCU) per semester, dan memantau siapa saja yang belum diperiksa — semuanya dari satu dashboard.

Dibangun dengan Laravel 8 dan template admin Tailwind (Rubick).

---

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Instalasi Lokal](#instalasi-lokal)
- [Instalasi dengan Docker](#instalasi-dengan-docker)
- [Akun Demo](#akun-demo)
- [Konfigurasi yang Disarankan](#konfigurasi-yang-disarankan)
- [Mengubah Tampilan (CSS/JS)](#mengubah-tampilan-cssjs)
- [Perintah Artisan Khusus](#perintah-artisan-khusus)
- [Struktur Proyek](#struktur-proyek)
- [Konsep Penting](#konsep-penting)
- [Lisensi Template](#lisensi-template)
- [Kontribusi](#kontribusi)

---

## Fitur

**Dashboard**
- Ringkasan kunjungan hari ini, bulan ini (dibanding bulan lalu), dan tren 7 hari.
- Cakupan raport kesehatan semester berjalan + daftar siswa prioritas yang belum diperiksa.
- Filter per jenjang dan kelas.

**Kunjungan UKS**
- Catat kunjungan (keluhan, diagnosis, tindakan, obat, tingkat keluhan) lewat modal, dengan chip keluhan cepat.
- Opsional: sekaligus catat sebagai riwayat penyakit (dengan kode ICD-10).
- Ubah dan hapus kunjungan, filter status/prioritas/tanggal, export Excel & PDF.

**Raport Kesehatan (MCU)**
- Input per semester: berat/tinggi badan (BMI otomatis), penglihatan, pendengaran, gigi; gula darah & kolesterol khusus guru.
- Mode **Simpan & Berikutnya** untuk mengisi raport satu kelas secara beruntun.
- Rekap sudah/belum diperiksa per siswa & guru, penanda "perlu tindak lanjut".
- Unduh raport per anggota dalam PDF, export rekap Excel & PDF.

**Siswa & Guru**
- CRUD data siswa, guru, dan tenaga kependidikan.
- Import massal dari CSV (template tersedia di aplikasi; data yang sudah ada diperbarui, bukan diduplikasi).
- Profil kesehatan: riwayat kunjungan, histori raport, riwayat penyakit.
- Kenaikan kelas SD/SMP dan kelulusan SMP otomatis setiap awal tahun ajaran.

**Umum**
- Pencarian cepat siswa/guru dari top bar (tekan `/`).
- Mode gelap dan 5 pilihan warna tema.

---

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | Laravel 8.x, PHP 8.1+ |
| Database | MySQL 8 (atau MariaDB 10.3+) |
| PDF | dompdf 3.1 |
| Frontend | Blade, Tailwind CSS 3, `@left4code/tw-starter`, Feather Icons, Tippy.js |
| Build aset | Laravel Mix 6 + PostCSS |

---

## Persyaratan

- **PHP 8.1 atau lebih baru** dengan ekstensi `pdo_mysql`, `mbstring`, `gd`, `dom`, `xml`, `bcmath`.
  > `composer.json` masih menulis `^7.3|^8.0`, tetapi kode memakai sintaks PHP 8.1 (array unpacking dengan string key). Gunakan PHP 8.1+.
- Composer 2
- MySQL 8 / MariaDB
- Node.js 16+ dan npm — **hanya jika** Anda ingin mengubah CSS/JS. Hasil build sudah ada di `public/dist`.

Di Windows, [Laragon](https://laragon.org/) sudah menyediakan PHP, MySQL, dan Composer.

---

## Instalasi Lokal

```bash
# 1. Clone hasil fork Anda
git clone https://github.com/<username-anda>/uks-sekolah.git
cd uks-sekolah

# 2. Install dependency PHP
composer install

# 3. Siapkan file environment
cp .env.example .env
php artisan key:generate
```

Buat database kosong (misalnya `uks_sekolah`), lalu sesuaikan `.env`:

```dotenv
APP_NAME="UKS Sekolah"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uks_sekolah
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 4. Buat tabel dan isi data contoh
php artisan migrate --seed

# 5. Jalankan aplikasi
php artisan serve
```

Buka <http://localhost:8000> dan masuk dengan [akun demo](#akun-demo).

> Tidak ingin data contoh? Jalankan `php artisan migrate` saja, lalu buat akun lewat `php artisan tinker` (lihat [Akun Demo](#akun-demo)).

---

## Instalasi dengan Docker

`docker-compose.yml` menyediakan tiga service: aplikasi (port **8000**), MySQL (port **3306**), dan phpMyAdmin (port **8080**).

```bash
cp .env.example .env
```

Ubah bagian database di `.env` agar mengarah ke container MySQL:

```dotenv
DB_HOST=mysql
DB_DATABASE=uks_sekolah
DB_USERNAME=root
DB_PASSWORD=root
```

```bash
docker compose up -d --build

# Folder proyek di-mount ke container, sehingga vendor/ dari image tertimpa.
# Install ulang dependency di dalam container:
docker compose exec app composer install

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

- Aplikasi: <http://localhost:8000>
- phpMyAdmin: <http://localhost:8080> (user `root`, password `root`)

> Jika MySQL belum siap saat `migrate` dijalankan, tunggu beberapa detik lalu ulangi perintahnya.

---

## Akun Demo

Dibuat oleh `UksDemoSeeder` (`php artisan migrate --seed`):

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@sekolah.sch.id` | `admin123` |
| Petugas | `petugas@sekolah.sch.id` | `petugas123` |

Seeder juga membuat jenjang TK/SD/SMP serta contoh siswa, guru, kunjungan, dan raport.

> ⚠️ **Ganti password ini sebelum dipakai sungguhan.** Untuk membuat akun baru tanpa seeder:
>
> ```bash
> php artisan tinker
> >>> App\Models\User::create(['name' => 'Nama Petugas', 'email' => 'petugas@sekolah.sch.id', 'password' => bcrypt('password-kuat'), 'role' => 'petugas']);
> ```
>
> Jika tidak memakai seeder, isi juga tabel `jenjang` dengan `TK`, `SD`, dan `SMP`.

---

## Konfigurasi yang Disarankan

Bawaan `config/app.php` adalah `timezone = UTC` dan `locale = en`. Untuk sekolah di Indonesia, ubah menjadi:

```php
// config/app.php
'timezone' => 'Asia/Jakarta',   // atau Asia/Makassar / Asia/Jayapura
'locale'   => 'id',
```

Tanpa perubahan ini, tanggal default "hari ini" bisa meleset pada pagi hari (selisih zona waktu), dan nama hari/bulan tampil dalam bahasa Inggris.

Untuk produksi:

```dotenv
APP_ENV=production
APP_DEBUG=false
```

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Mengubah Tampilan (CSS/JS)

Hasil build (`public/dist/css/app.css`, `public/dist/js/app.js`) **sudah di-commit**, jadi aplikasi bisa langsung jalan tanpa Node.js. Build ulang hanya jika Anda mengubah file di `resources/css`, `resources/js`, atau menambah kelas Tailwind baru di Blade.

```bash
npm install
npm run prod     # build produksi (minify + versioning)
npm run dev      # build development
```

Catatan:
- Tailwind memindai `resources/**/*.php`. Tulis nama kelas secara utuh — kelas yang dirangkai dinamis seperti `bg-{{ $warna }}` tidak akan ter-generate.
- `npm run prod` menjalankan PostCSS (Tailwind) ke `public/dist/css/_app.css`, lalu Mix mem-bundle ke `public/dist/css/app.css`.
- `resources/js/app.js` hanya meng-import modul yang dipakai. Banyak file lain di `resources/js` adalah bawaan template dan sengaja tidak di-bundle.

---

## Perintah Artisan Khusus

### Kenaikan kelas & kelulusan

```bash
# Pratinjau (tidak mengubah data)
php artisan uks:promote-students

# Simulasi pada tanggal tertentu
php artisan uks:promote-students --date=2026-07-01

# Jalankan perubahan
php artisan uks:promote-students --execute
```

Siswa SD kelas 1–5 dan SMP kelas 7–8 naik satu tingkat (akhiran kelas dipertahankan, mis. `2A` → `3A`). Siswa SMP kelas 9 diluluskan dan dinonaktifkan. Siswa TK dan SD kelas 6 **tidak** diproses otomatis, jadi perbarui datanya secara manual (mis. pindah ke jenjang SMP atau nonaktifkan). Kelas harus diawali angka tingkat (`2A`, `7B`) agar dikenali. Setiap perubahan dicatat di tabel `riwayat_kelas`, sehingga perintah aman dijalankan ulang.

Perintah ini terjadwal otomatis **setiap 1 Juli pukul 00:05**. Agar jadwal berjalan, aktifkan scheduler Laravel di server:

```cron
* * * * * cd /path/ke/uks-sekolah && php artisan schedule:run >> /dev/null 2>&1
```

---

## Struktur Proyek

```
app/
├── Console/Commands/PromoteStudents.php   # kenaikan kelas otomatis
├── Http/
│   ├── Controllers/                       # Dashboard, Anggota, Kunjungan, Pemeriksaan, Export
│   ├── Requests/                          # validasi form (Anggota, Kunjungan, Pemeriksaan)
│   └── View/Composers/                    # menu, dark mode, tema warna
├── Main/SideMenu.php                      # daftar menu sidebar
├── Models/                                # Anggota, KunjunganUks, PemeriksaanKesehatan, ...
└── Support/TahunAjaran.php                # aturan semester & tahun ajaran

resources/
├── css/components/_uks.css                # gaya khusus aplikasi
├── js/uks.js                              # perilaku UI (pencarian, modal, filter, BMI, dll.)
└── views/
    ├── components/anggota-picker.blade.php  # kolom pencarian anggota (autocomplete)
    ├── layout/                            # layout utama, top bar, flash message
    ├── exports/                           # template Excel/PDF
    └── pages/                             # dashboard, anggota, kunjungan, pemeriksaan
```

Tabel utama: `users`, `jenjang`, `anggota` (siswa/guru/tendik), `kunjungan_uks`, `pemeriksaan_kesehatan`, `riwayat_penyakit`, `riwayat_kelas`.

---

## Konsep Penting

**Semester & tahun ajaran** — diatur di `App\Support\TahunAjaran`:
- Semester 1: Juli–Desember, Semester 2: Januari–Juni.
- Tahun ajaran ditulis dengan tahun awalnya (`2025` = 2025/2026).
- Raport baru hanya bisa diinput untuk semester berjalan; raport lama tetap bisa diubah.
- Satu anggota hanya punya satu raport per semester per tahun ajaran.

**Anggota** — satu tabel untuk siswa, guru, dan tenaga kependidikan (kolom `tipe`). Raport kesehatan hanya untuk siswa dan guru; cek lab (gula darah, kolesterol) hanya untuk guru. Anggota nonaktif (mis. sudah lulus) tidak muncul di pencarian dan daftar raport.

**Filter** — gunakan scope `Anggota::filter([...])` (jenjang, kelas, pencarian nama/NIS) dan `KunjunganUks::filterAnggota([...])` agar hasil di layar dan hasil export selalu konsisten.

---

## Lisensi Template

Tampilan aplikasi memakai **Rubick – Tailwind HTML Admin Template** dari [Left4code](https://themeforest.net/user/left4code/portfolio), yang merupakan produk **berlisensi berbayar**. Jika Anda memakai hasil fork ini untuk proyek sendiri, pastikan Anda memiliki lisensi template yang valid, atau ganti tampilannya dengan template lain.

Framework Laravel dirilis di bawah [lisensi MIT](https://opensource.org/licenses/MIT).

---

## Kontribusi

1. Fork repositori ini, lalu buat branch baru: `git checkout -b fitur/nama-fitur`.
2. Jika mengubah CSS/JS, jalankan `npm run prod` dan sertakan hasil build di `public/dist`.
3. Uji alur utama secara manual: login, catat kunjungan, input raport, import CSV, dan export.
4. Kirim pull request dengan deskripsi singkat tentang perubahan dan alasannya.

Laporan bug dan saran fitur dapat dikirim melalui tab **Issues**.
