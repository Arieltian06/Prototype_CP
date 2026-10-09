# Progress EduPesisir Bengkulu

Status akhir: **selesai dan dapat didemonstrasikan**, UI diperbarui dan diverifikasi 2 Oktober 2026. Workspace: `D:\Prototype_CP`. Aplikasi lokal: http://127.0.0.1:8000.

## Milestone

| Milestone | Status | Hasil dan file penting |
|---|---|---|
| 1. Database, model, seeder | Selesai | 11 tabel domain, FK dan unique constraint; `database/`, `app/Models/`; migrasi dan seeder MariaDB berhasil |
| 2. Authentication admin | Selesai | Guard admin, hash, CSRF, session regeneration/invalidation, throttle; `config/auth.php`, `Admin/AuthController.php` |
| 3. Dashboard | Selesai | Statistik database, aksi cepat, sidebar/topbar responsif |
| 4. CRUD tempat | Selesai | Upload tervalidasi, preview, transaksi relasi, penggantian/penghapusan foto dan cleanup Storage |
| 5. CRUD pembelajaran | Selesai | Jenjang, mata pelajaran, sekolah; validasi dan perlindungan data yang masih digunakan |
| 6. CRUD SAW | Selesai | Kriteria, bobot, skala kesesuaian, nilai manual; perubahan skala memperbarui pivot |
| 7. Haversine | Selesai | `DistanceService.php`; km, validasi koordinat, kasus nol/simetri/antipoda |
| 8. SAW | Selesai | `SawService.php`; benefit/cost, bobot ternormalisasi, preferensi, ranking otomatis, kebijakan nol |
| 9. RecommendationService | Selesai | Kandidat aktif, relasi terpilih, jarak, nilai kriteria dan alasan pengecualian |
| 10. Halaman publik | Selesai | Beranda, katalog, detail, pencarian, tentang; ilustrasi dan font Inter lokal |
| 11. Hasil rekomendasi | Selesai | Kartu ranking, skor, penjelasan, matriks keputusan/normalisasi, kontribusi preferensi |
| 12. Peta | Selesai | Leaflet, tile OSM, origin/marker ranking, popup aman dan fitBounds |
| 13. UI refinement | Selesai | Screenshot desktop/mobile ditinjau; lebar 320/390/768/1280/1440px diuji sesuai halaman |
| 14. Testing | Selesai | 93 tes backend dan 6 tes browser lulus; upload, auth, CRUD, validasi, jarak, SAW, snapshot |
| 15. Final bug fix | Selesai | Error Blade peta, nested-array input, dan render old input diperbaiki; README lengkap |

## Verifikasi yang dilakukan

- Laravel 12.69.2 berjalan dengan PHP 8.2.12; MariaDB 10.4.32 database `edupesisir_prototype`.
- `php artisan migrate:fresh --seed` berhasil pada database prototype khusus pada sesi pembangunan; semua migration kini berstatus Ran.
- Seeder: 3 jenjang, total 18 entri mata pelajaran, 6 sekolah demo, 6 tempat prototype, 3 kriteria, skala 1–5, admin demo.
- `php artisan storage:link` berhasil.
- `php artisan route:list`: 57 route aplikasi terdaftar (di luar route vendor).
- Suite penuh `php artisan test --compact`: **93 passed, 560 assertions** sebelum empat assertions tambahan untuk halaman arsip SAW. Verifikasi tambahan test snapshot/arsip: **1 passed, 54 assertions**. Seluruh implementasi lulus.
- `npm run test:e2e`: **6 passed**, mencakup desktop/public, ranking dan accordion matriks, peta, geolocation ditolak, mobile, admin CRUD/logout, CSRF, tablet dan ponsel kecil.
- `php artisan view:cache`, `php vendor/bin/pint --test`, `composer validate`, dan `node --check public/js/edupesisir.js` berhasil.
- PHPUnit memakai SQLite `:memory:`; tidak mereset data MariaDB. Browser memakai database demo dan menghasilkan beberapa snapshot contoh.
- Bukti tampilan lokal: `artifacts/home-desktop.png`, `home-mobile.png`, `recommendation-desktop.png`, `recommendation-mobile.png`, `admin-desktop.png`, `admin-mobile.png`.

## Keputusan implementasi

- `tempat_jenjang` memisahkan kesesuaian jenjang dari kesesuaian mata pelajaran.
- `sumber_nilai` pada kriteria memilih jenjang/mapel/jarak/manual; `penilaian_tempat` menyimpan kriteria manual tambahan.
- Bobot demo C1=0,30, C2=0,45, C3=0,25 disimpan di database dan dinormalisasi saat menghitung; bukan bobot final penelitian.
- UUID dan snapshot JSON mempertahankan data/matriks/bobot/skor historis walaupun data sumber diubah atau dihapus.
- Benefit seluruhnya nol memberikan r=0. Cost minimum nol memberikan r=1 untuk nilai nol dan r=0 untuk nilai positif; jika semua nol, semuanya r=1.
- Semua perhitungan tanpa pembulatan antara. Pembulatan hanya pada tampilan. Jarak Haversine merupakan garis lurus.
- Aset Bootstrap, ikon, Leaflet, Inter, dan ilustrasi tersimpan lokal. Tidak ada build frontend yang dibutuhkan; npm hanya untuk tes browser.

## Masalah yang sudah diselesaikan

- JSON inline Blade pada detail tempat memicu parse error: dipindahkan ke variabel data sebelum serialisasi.
- Scope validasi dengan input array bersarang dan filter pencarian array dapat menimbulkan 500: validasi/scalar scope diperbaiki.
- Old input array setelah redirect validasi menimbulkan 500 di form publik/admin/login: view memulihkan hanya nilai scalar, tanpa melonggarkan validasi.
- Selector tes browser memilih label IPA milik jenjang lain yang disabled: tes kini memilih opsi aktif sesuai jenjang.

## Batas dan pekerjaan lanjutan penelitian

Tidak ada fungsi inti yang tertunda. Data sekolah, koordinat, biaya, jam operasional, penilaian, ilustrasi, dan bobot masih demo/prototype; belum merupakan hasil survei resmi. Tile peta memerlukan internet. Penelitian lanjutan membutuhkan validasi lapangan, foto asli, penilaian pendidik, dan bobot final.

Untuk melanjutkan pengembangan, gunakan proyek yang ada. Jangan membuat ulang Laravel atau mereset database tanpa kebutuhan. Panduan menjalankan ulang dan kredensial admin demo ada di README.md.

## Penyempurnaan keterbacaan

- Menindaklanjuti masukan bahwa font terlalu kecil: teks utama publik menjadi 18px desktop / 17px mobile, kontrol 16px, label dan teks pendukung 14–16px.
- Panel admin memakai teks utama, navigasi, tabel, form dan tombol 16px; informasi pendukung minimal 14px. Warna teks sekunder dibuat lebih gelap.
- Jarak antarbaris, lebar sidebar, pembungkusan teks pada kartu/tombol/ranking, dan susunan kartu statistik disesuaikan untuk font lebih besar.
- Ukuran font kecil pada media query mobile diganti agar teks tidak kembali mengecil. CSS menggunakan struktur multiline agar mudah dipelihara.
- Layout memuat versi CSS berdasarkan waktu modifikasi file, sehingga pembaruan tampilan ikut termuat saat halaman disegarkan.
- Enam pengujian browser yang sudah ada kembali lulus setelah perubahan, termasuk rekomendasi, admin dan lebar layar 320/390/768px. Tidak ada perubahan pada perhitungan SAW atau database.

## Pembaruan visual publik — 2 Oktober 2026

- Scope dikoreksi pemilik: hanya UI, semua fitur dan alur lama dipertahankan. Tidak ada fitur jurnal, perbandingan, atau halaman atlas baru; seluruh rancangan tambahan tersebut sudah dikeluarkan.
- Identitas editorial pesisir: warna kertas hangat/hijau laut, sampul ilustrasi SVG khusus, judul besar dengan aksen serif, susunan tempat bernomor, serta navigasi dan footer baru.
- Halaman katalog, detail, form rekomendasi, hasil ranking, peta, dan tabel SAW memakai bahasa visual yang sama. Aktivitas yang sudah ada ditampilkan sebagai daftar bernomor.
- Teks utama tetap 18px desktop/17px ponsel. Perbaikan gutter halaman detail menjaga tampilan ponsel tidak melebar.
- Notice Data Prototype serta anotasi seed di halaman publik dihapus pada lapisan tampilan melalui `PublicContent`; data, flag internal, perhitungan, dan snapshot tidak diubah.
- Stylesheet publik `atlas.css` dan `public-pages.css` memakai versi berdasarkan waktu modifikasi; panel admin tetap menggunakan stylesheet sebelumnya.
- Verifikasi backend: 93 tes / 564 assertions lulus. Setelah penyempurnaan tampilan teks snapshot, 9 tes presentasi / 60 assertions lulus. Blade dan Pint lulus.
- Enam skenario browser lulus: lima langsung pada suite, satu skenario mobile diulang dan lulus setelah penguatan batas lebar grid ranking serta pembungkusan caption panjang. Satu kejadian overflow awal 504px pada viewport390 tidak berhasil direproduksi, termasuk ketika gambar/font diblokir; grid diperkuat tanpa menyembunyikan overflow.
- Tampilan publik ditinjau pada 320/390/768/1024/1440px. Bukti visual: `artifacts/redesign-home-viewport.png`, `redesign-mobile-viewport.png`, `redesign-catalog-viewport.png`, serta `inner-form-desktop.png`, `inner-detail-fixed390.png`, dan `inner-results-desktop.png`.

## Lanjutan penyempurnaan UI — 2 Oktober 2026

- Katalog ditata ulang sebagai koleksi editorial dua kolom: nomor koleksi mengikuti pagination dan urutan nama, gambar lebih besar, ringkasan deskripsi, alamat, pelajaran, biaya, serta tautan detail. Pencarian/filter dan pagination tetap menggunakan controller lama.
- Detail tempat memakai judul/alamat di bagian awal, gambar besar, lembar informasi kunjungan berwarna hijau laut, aktivitas bernomor, kelompok mata pelajaran, dan peta yang tetap memakai integrasi Leaflet sebelumnya.
- Halaman Tentang menggunakan pembagian bab, catatan margin, dan penulisan pecahan pada rumus SAW. Penjelasan metode dan batas interpretasi dipertahankan.
- CSS baru dimuat hanya pada halaman terkait: `place-catalog.css`, `place-detail.css`, dan `about-editorial.css`. Tidak ada route, fitur, JavaScript, controller, atau database baru.
- Pengujian relevan yang sudah tersedia: 9 tes presentasi / 60 assertions dan 2 skenario browser navigasi publik serta responsivitas lulus. Pencarian Pantai Panjang menghasilkan satu kartu, reset mengembalikan enam kartu, dan hasil kosong ditampilkan dengan benar.
- Katalog dan dua detail tempat diperiksa pada 320/390/768/1024/1440px: tanpa overflow atau error JavaScript, teks utama 17px ponsel / 18px desktop. Bukti visual tersedia pada `artifacts/catalog-editorial-viewport.png`, `catalog-editorial-mobile.png`, `detail-editorial-viewport.png`, dan `detail-editorial-mobile.png`.
- Halaman Tentang juga lulus pemeriksaan HTTP 200, empat rumus, tautan rekomendasi, dan lebar 320/390/768/1440px tanpa overflow atau error JavaScript. Screenshot: `storage/app/private/about-1440.png` dan `about-390.png`. Kompilasi seluruh Blade lulus setelah semua perubahan.
