# EduPesisir Bengkulu

**Temukan Tempat Belajar Edukatif yang Sesuai untukmu.**

## Tentang

Prototype Capstone Project Rekayasa Perangkat Lunak, Program Studi Informatika, Universitas Bengkulu, 2026. Aplikasi membantu siswa SD, SMP, dan SMA menemukan potensi pembelajaran kontekstual di pesisir Kota Bengkulu menggunakan Simple Additive Weighting (SAW). Guru dan sekolah dapat memakainya untuk meninjau kegiatan luar kelas.

## Tujuan

Menghubungkan jenjang, mata pelajaran, kesesuaian edukatif, serta jarak dari sekolah dengan ranking yang dapat diperiksa proses perhitungannya. Peringkat tertinggi adalah hasil kriteria dan bobot yang digunakan; bukan jaminan bahwa tempat tersebut selalu terbaik atau layak dikunjungi tanpa verifikasi.

## Fitur

### User

- Akses publik tanpa login: beranda, katalog dengan pencarian/filter/pagination, detail tempat, dan halaman tentang metode.
- Form rekomendasi memfilter mata pelajaran dan sekolah sesuai jenjang; backend memvalidasi hubungan tersebut.
- Sekolah menjadi lokasi asal utama. Geolocation opsional; penolakan izin tetap memungkinkan pencarian melalui sekolah.
- Haversine dihitung di backend, ranking SAW otomatis, skor, penjelasan, dan peta seluruh alternatif.
- Detail bobot awal/normalisasi, matriks keputusan, matriks normalisasi, kontribusi preferensi, dan ranking.
- Snapshot hasil mempertahankan data perhitungan walaupun admin mengubah tempat atau bobot.
- Tampilan responsif, state kosong/error/loading, navigasi keyboard, serta ilustrasi SVG lokal.

Pembaruan visual 2 Oktober 2026 mempertahankan fitur dan alur yang sama. Halaman publik memakai gaya editorial pesisir: warna kertas hangat, tinta hijau laut, ilustrasi sampul khusus, daftar tempat bernomor, serta judul dan jarak baca yang lebih lega. Tema ada di `public/css/atlas.css` dan `public/css/public-pages.css`; aset ilustrasi ada di `public/images/pesisir-editorial.svg`. Tidak diperlukan build frontend.

Penyempurnaan berikutnya menata katalog seperti koleksi editorial, detail tempat seperti panduan kunjungan, dan halaman Tentang sebagai bab bacaan dengan rumus yang terstruktur. Stylesheet khusus `place-catalog.css`, `place-detail.css`, dan `about-editorial.css` dimuat hanya pada halaman terkait. Fitur, controller, dan perhitungan tetap sama.

### Admin

- Login menggunakan guard `admin`, password hash, session regeneration, logout invalidation, dan pembatasan percobaan login.
- Dashboard statistik dan CRUD tempat, jenjang, mata pelajaran, sekolah, kriteria/bobot, serta skala kesesuaian.
- Upload foto JPG/JPEG/PNG/WebP melalui Laravel Storage, preview, validasi tipe/ukuran, dan pengelolaan file lama.
- Penilaian kesesuaian tempat–jenjang dan tempat–mapel; nilai manual untuk kriteria tambahan.
- Riwayat hasil perhitungan dengan snapshot yang dapat ditinjau kembali.
- Pencarian/pagination tabel, konfirmasi hapus, dan perlindungan relasi yang masih digunakan.

## Teknologi

Laravel 12, PHP 8.2+, Eloquent, Form Request, Blade, Bootstrap 5.3.8, Bootstrap Icons 1.13.1, CSS/JavaScript khusus, Leaflet 1.9.4, OpenStreetMap, MySQL/MariaDB. PHPUnit untuk pengujian backend dan Playwright untuk pengujian browser.

Laravel 12 dipilih karena environment lokal PHP 8.2.12; Laravel 13 memerlukan PHP 8.3. Referensi: https://laravel.com/framework/docs/releases. Versi dependensi tepat dikunci di `composer.lock` dan `package-lock.json`.

Aset frontend tersedia di `public/vendor`, sehingga tidak memerlukan CDN atau build Vite. Leaflet ringan dan tidak memerlukan API key. Tile OpenStreetMap tetap memerlukan internet. Lisensi library disertakan di direktori masing-masing.

## Requirement

- PHP 8.2+ dengan PDO MySQL, mbstring, openssl, fileinfo, DOM/XML, curl, zip; PDO SQLite untuk tes terisolasi.
- Composer 2; MySQL 8+ atau MariaDB 10.4+.
- Node.js 20+ hanya diperlukan untuk menjalankan pengujian browser.
- Pada Windows, PHP dan MariaDB XAMPP dapat digunakan. Jalankan service MySQL melalui XAMPP terlebih dahulu.

## Installation

```sh
composer install
cp .env.example .env
php artisan key:generate
```

PowerShell menggunakan `Copy-Item .env.example .env` sebagai pengganti `cp` bila diperlukan. Jangan menimpa `.env` yang sudah dikonfigurasi.

Buat database kosong khusus aplikasi:

```sql
CREATE DATABASE edupesisir_prototype CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Konfigurasikan `.env` sesuai instalasi lokal:

```dotenv
APP_NAME="EduPesisir Bengkulu"
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edupesisir_prototype
DB_USERNAME=root
DB_PASSWORD=
```

```sh
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

Buka http://127.0.0.1:8000. Tidak perlu `npm run build`: CSS/JS aplikasi dan distribusi vendor sudah siap dilayani oleh Laravel.

Untuk **mengulang demo pada database khusus yang boleh direset** gunakan `php artisan migrate:fresh --seed`. Perintah ini menghapus seluruh tabel pada database yang dikonfigurasi. Gunakan `migrate --seed` untuk instalasi baru tanpa perintah reset.

## Admin Demo

- URL: `/admin/login`
- Email: `admin@edupesisir.test`
- Password: `password123`

Kredensial ini **hanya untuk DEMO lokal**, password disimpan sebagai hash. Sebelum penggunaan di luar demo, ubah kredensial, aktifkan HTTPS, atur `APP_DEBUG=false`, dan gunakan kredensial database aplikasi tersendiri.

## Database

| Tabel | Fungsi |
|---|---|
| `admins` | Identitas login admin, PK `id_admin` |
| `jenjang_pendidikan` | Jenjang, PK `id_jenjang` |
| `mata_pelajaran` | Mata pelajaran per jenjang, PK `id_mapel` |
| `sekolah` | Sekolah dan koordinat asal, PK `id_sekolah` |
| `tempat_belajar` | Informasi, foto, koordinat, status dan label prototype; PK `id_tempat` |
| `kriteria` | Kode, jenis, bobot, dan sumber nilai; PK `id_kriteria` |
| `nilai_kesesuaian` | Skala nilai dan label yang dapat dikelola |
| `tempat_mapel` | Relasi tempat–mapel dengan nilai kesesuaian |
| `tempat_jenjang` | Relasi tempat–jenjang dengan nilai kesesuaian independen |
| `penilaian_tempat` | Nilai manual tempat untuk kriteria tambahan |
| `perhitungan_saw` | UUID dan snapshot JSON input/hasil |

Migration juga menyertakan tabel infrastruktur Laravel untuk session, cache, dan queue. Foreign key membatasi penghapusan jenjang yang dipakai sekolah/mapel. Relasi pivot/penilaian terhapus secara cascade bersama induknya. Duplikasi pasangan relasi dicegah unique constraint.

Penambahan `tempat_jenjang` diperlukan karena kesesuaian jenjang tidak selalu identik dengan mata pelajaran yang tersedia. C1, C2, dan C3 dihitung dinamis saat pencarian; `penilaian_tempat` digunakan untuk kriteria tambahan bersumber `manual`, sehingga struktur proposal tetap dipertahankan.

## Metode SAW

Kriteria awal disimpan dalam database:

| Kode | Kriteria | Jenis | Bobot demo |
|---|---|---|---:|
| C1 | Kesesuaian jenjang | benefit | 0.30 |
| C2 | Kesesuaian mata pelajaran | benefit | 0.45 |
| C3 | Jarak Haversine (km) | cost | 0.25 |

**Bobot ini adalah nilai awal prototype, bukan nilai final penelitian.** Admin dapat mengubahnya. Setiap perhitungan menggunakan `w_normalized = w / sum(w)`; semua bobot harus nonnegatif dan jumlahnya harus positif.

1. Pilih tempat aktif yang mempunyai relasi untuk jenjang dan mapel terpilih.
2. Hitung C1 dari `tempat_jenjang`, C2 dari `tempat_mapel`, C3 dari Haversine.
3. Baca kriteria tambahan dari `penilaian_tempat` sesuai `sumber_nilai`.
4. Bangun matriks keputusan X untuk seluruh alternatif yang memenuhi persyaratan.
5. Benefit: `r_ij = x_ij / max_i(x_ij)`.
6. Cost: `r_ij = min_i(x_ij) / x_ij`.
7. Preferensi: `V_i = sum_j(w_j_normalized * r_ij)`.
8. Urutkan V menurun. Jika skor persis sama, urutkan ID tempat naik agar stabil.

Perhitungan memakai presisi floating point tanpa pembulatan antara. Pembulatan hanya pada tampilan; tabel detail dan kartu menggunakan snapshot perhitungan yang sama. Skor SAW bukan probabilitas atau persentase keberhasilan belajar.

### Nol dan data tidak lengkap

- Kolom benefit seluruhnya nol: semua r bernilai 0 (tidak memberi kontribusi).
- Cost minimum nol: nilai x=0 memperoleh r=1; x positif memperoleh r=0. Bila seluruhnya nol, semua r=1. Ini adalah kebijakan batas pembagian nol prototype, dijelaskan terbuka untuk pembahasan akademik.
- Alternatif dengan koordinat tidak valid atau nilai kriteria manual belum lengkap dikeluarkan dan alasannya dicatat.
- Tempat yang tidak berelasi dengan jenjang/mapel terpilih tidak menjadi alternatif; nilai yang tidak tersedia tidak ditebak.
- Kriteria kosong, bobot tidak valid, atau koordinat asal tidak valid menghasilkan pesan yang dapat ditindaklanjuti.
- Tidak ada alternatif menghasilkan halaman kosong yang dapat digunakan untuk mencari ulang.

### Skala dan kriteria tambahan

Skala awal: 1 Sangat Rendah, 2 Rendah, 3 Cukup, 4 Sesuai, 5 Sangat Sesuai. Admin dapat menambah/mengubah skala. Pengubahan nilai skala memperbarui pivot terkait dalam transaksi; skala yang masih dipakai tidak dapat langsung dihapus.

Untuk menambah C4, buat kriteria dengan sumber `manual`, tentukan benefit/cost dan bobot, lalu isi nilainya di form masing-masing tempat. Semua kriteria ikut dimasukkan ke matriks secara dinamis. Sumber dinamis yang sama tidak dapat diduplikasi melalui panel admin.

## Haversine

`DistanceService::calculateDistance(lat1, lng1, lat2, lng2)` mengubah derajat menjadi radian, lalu menghitung:

```text
a = sin²(delta_lat / 2) + cos(lat1) * cos(lat2) * sin²(delta_lng / 2)
c = 2 * atan2(sqrt(a), sqrt(1 - a))
d = 6371.0088 * c
```

Implementasi menggunakan bentuk ekuivalen `c = 2 * asin(sqrt(a))`. Hasil dalam kilometer merupakan **jarak garis lurus**, bukan jarak jalan, waktu tempuh, atau rute aman. Latitude divalidasi -90 sampai 90 dan longitude -180 sampai 180. Nilai `a` dijepit ke [0,1] untuk mengatasi galat floating point.

## Flow Rekomendasi

Jenjang → mapel → sekolah → validasi backend → koordinat asal → alternatif → Haversine → nilai kriteria → normalisasi bobot dan matriks → preferensi → ranking → snapshot → kartu hasil, peta, dan tabel akademik.

Geolocation membutuhkan secure context (HTTPS atau localhost) dan izin browser. Sekolah tetap wajib. Bila izin ditolak, tidak tersedia, atau waktu akses habis, pemilihan sekolah tetap berfungsi. Jika lokasi perangkat digunakan, koordinatnya tersimpan bersama hasil. Hasil dapat diakses oleh pemilik tautan UUID; riwayat lengkap hanya melalui admin. Hindari memasukkan data pribadi dalam data demo.

## Route Penting

| Route | Fungsi |
|---|---|
| `/` | Beranda |
| `/tempat-belajar` | Katalog |
| `/tempat-belajar/{id}` | Detail tempat aktif |
| `/rekomendasi` | Form dan POST pencarian |
| `/rekomendasi/hasil/{uuid}` | Snapshot ranking dan detail SAW |
| `/tentang` | Informasi sistem/metode |
| `/admin/login` | Login admin |
| `/admin/dashboard` | Statistik |
| `/admin/tempat` | CRUD tempat dan penilaian |
| `/admin/jenjang`, `/admin/mapel`, `/admin/sekolah` | CRUD pembelajaran |
| `/admin/kriteria` | CRUD kriteria/bobot |
| `/admin/kesesuaian`, `/admin/skala` | Penilaian dan skala |
| `/admin/perhitungan` | Riwayat SAW |

## Data Prototype

Seeder mengisi 3 jenjang, total 18 entri mata pelajaran yang terbagi dalam ketiga jenjang, 6 sekolah fiktif berlabel Demo, 6 tempat contoh, 3 kriteria, skala 1–5, serta relasi penilaian.

Contoh tempat: Pantai Panjang, Pantai Jakat, kawasan mangrove, kawasan Benteng Marlborough, Tapak Paderi, dan ruang belajar pesisir. Nama tertentu merujuk kawasan dikenal, tetapi **koordinat, alamat rinci, biaya, jam, deskripsi, aktivitas, dan nilai kesesuaian belum diverifikasi sebagai data resmi**. Sekolah adalah data simulasi. Semua ilustrasi merupakan SVG konsep, bukan foto dokumentasi tempat. Sesuai permintaan pemilik proyek, label Data Prototype dan anotasi demo tidak ditampilkan pada halaman publik. `App\Support\PublicContent` membersihkan frasa anotasi seed yang dikenal hanya saat presentasi; record database, penanda internal `is_prototype`, dan snapshot asli tetap utuh. Data perlu diganti/divalidasi melalui survei dan kajian pendidik sebelum penelitian final.

Seeder idempotent untuk instalasi demo; pemanggilan ulang mengembalikan nilai demo pada entri seed. Jangan menjalankannya terhadap data penelitian yang telah disunting tanpa memahami konsekuensinya.

## Testing

```sh
php artisan route:list
php artisan test
```

PHPUnit menggunakan SQLite `:memory:` yang dipaksa melalui `phpunit.xml`, sehingga tidak menghapus database MariaDB demo. Pengujian meliputi Haversine, benefit/cost, fixture SAW terhitung, ranking, nol, bobot invalid, otentikasi, validasi lintas jenjang, CRUD/upload, snapshot, dan kriteria manual.

Verifikasi pembaruan UI, 2 Oktober 2026: **93 tes backend (564 assertions) dan seluruh 6 skenario browser lulus**, dengan skenario mobile diulang setelah perbaikan batas lebar ranking. Kompilasi Blade dan Laravel Pint juga lulus. Hasil verifikasi awal migrasi/seeder MariaDB, 57 route aplikasi, dan validasi Composer tercatat di [PROGRESS.md](PROGRESS.md).

Pengujian browser dijalankan pada database demo yang sudah dimigrasi dan di-seed:

```sh
npm install
npx playwright install chromium
npm run test:e2e
```

Pada Windows dengan pembatasan PowerShell, gunakan `npm.cmd` dan `npx.cmd`. Playwright menjalankan/memakai server lokal port 8000, menguji tampilan desktop/mobile, login/CRUD, rekomendasi, peta, fallback lokasi, dan CSRF. Screenshot tersedia di `artifacts/` dan laporan di `playwright-report/`. Tes browser membuat snapshot rekomendasi demo dan membersihkan entri jenjang uji yang dibuatnya.

## Struktur Folder

```text
app/Models/                       Eloquent, relasi, casts, hash admin
app/Services/DistanceService.php   Haversine backend
app/Services/SawService.php        Mesin SAW murni
app/Services/RecommendationService.php  Pemilihan/nilai alternatif
app/Http/Requests/                 Validasi publik/admin
app/Http/Controllers/Admin/        Autentikasi, CRUD, dashboard, riwayat
resources/views/public/           Halaman publik dan hasil
resources/views/admin/            Panel admin
resources/views/components/       Komponen Blade bersama
public/css/                       Tema pesisir dan admin
public/js/                        Interaksi, geolocation, peta aman
public/images/                    Ilustrasi konsep lokal
public/vendor/                    Bootstrap, ikon, Leaflet, lisensi
routes/web.php, routes/admin.php   Rute publik/admin
database/migrations/              Skema dan constraint
database/seeders/                 Data demo
tests/Unit, tests/Feature          Pengujian PHPUnit
tests/Browser                     Pengujian Playwright
PROGRESS.md                       Status milestone dan hasil verifikasi
```

## Batas Prototype

Peta memerlukan akses tile OSM; aplikasi dan perhitungan tetap dapat berjalan tanpa tile. Foto asli, data survei sekolah/lokasi, bobot final penelitian, dan penilaian pendidik belum tersedia. Tidak ada klaim verifikasi lapangan atau optimasi rute perjalanan. Sistem siap untuk demonstrasi alur dan transparansi SAW dengan data simulasi.
