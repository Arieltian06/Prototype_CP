@extends('layouts.app')
@section('title', 'Tentang EduPesisir')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/about-editorial.css').'?v='.filemtime(public_path('css/about-editorial.css')) }}">
@endpush

@section('content')
<div class="about-editorial">
    <header class="about-cover">
        <div class="container">
            <div class="about-masthead"><span>TENTANG EDUPESISIR</span><span>BENGKULU <i class="bi bi-water" aria-hidden="true"></i></span></div>
            <div class="about-cover-grid">
                <div>
                    <span class="about-overline">Berangkat dari rasa ingin tahu.</span>
                    <h1>Lingkungan sekitar<br>adalah <em>ruang kelas</em><br>yang luas.</h1>
                </div>
                <div class="about-cover-note">
                    <span class="about-compass" aria-hidden="true"><i class="bi bi-compass"></i></span>
                    <p>EduPesisir Bengkulu membantu menemukan rekomendasi tempat belajar edukatif di kawasan pesisir bagi siswa Kota Bengkulu.</p>
                    <p>Mempertemukan kebutuhan pembelajaran dengan potensi alam, sejarah, dan budaya di sekitar kita.</p>
                </div>
            </div>
            <div class="about-cover-foot"><span>ALAM &middot; SEJARAH &middot; BUDAYA</span><span>Belajar dekat dengan kehidupan.</span></div>
        </div>
    </header>

    <section class="about-chapter container" aria-labelledby="about-purpose-title">
        <div class="about-margin">
            <span class="about-section-number" aria-hidden="true">01</span>
            <span class="about-overline">TITIK BERANGKAT</span>
            <div class="about-project-note"><span>SEBUAH CAPSTONE PROJECT</span><p>Program Studi Informatika<br>Universitas Bengkulu<br>2026</p></div>
        </div>
        <div class="about-reading">
            <h2 id="about-purpose-title">Pendidikan menjadi<br><em>titik berangkat.</em></h2>
            <p>Platform ini dikembangkan sebagai Capstone Project Program Studi Informatika, Universitas Bengkulu, tahun 2026. Fokus utamanya membantu siswa SD, SMP, SMA, serta pendidik mengenali lokasi dengan potensi edukatif dan menyusun pertimbangan kegiatan belajar di luar kelas.</p>
            <p>Siswa memilih jenjang pendidikan, mata pelajaran, dan sekolah. Sistem mengolah kesesuaian setiap tempat dan jarak dari titik asal, lalu menyajikan urutan alternatif beserta alasan, peta, dan rincian perhitungannya.</p>
            <ol class="about-process" aria-label="Alur rekomendasi">
                <li><span>Mulai dari</span><strong>Kebutuhan belajar</strong><i class="bi bi-arrow-right" aria-hidden="true"></i></li>
                <li><span>Pertimbangkan</span><strong>Kesesuaian &amp; jarak</strong><i class="bi bi-arrow-right" aria-hidden="true"></i></li>
                <li><span>Temukan</span><strong>Pilihan tempat</strong><i class="bi bi-arrow-up-right" aria-hidden="true"></i></li>
            </ol>
        </div>
    </section>

    <section class="about-method" aria-labelledby="about-method-title">
        <div class="container">
            <div class="about-method-heading"><span class="about-section-number" aria-hidden="true">02</span><div><span class="about-overline">DI BALIK REKOMENDASI</span><h2 id="about-method-title">Setiap urutan<br><em>punya perhitungan.</em></h2></div></div>
            <div class="about-method-grid">
                <div class="about-method-copy">
                    <h3>Simple Additive Weighting</h3>
                    <p>SAW memberi nilai preferensi dengan menjumlahkan nilai kriteria yang telah dinormalisasi dan dikalikan bobotnya.</p>
                    <dl class="about-criterion-types">
                        <div><dt><i class="bi bi-arrow-up-right" aria-hidden="true"></i> Benefit</dt><dd>Mengutamakan nilai besar.</dd></div>
                        <div><dt><i class="bi bi-arrow-down-right" aria-hidden="true"></i> Cost</dt><dd>Mengutamakan nilai kecil.</dd></div>
                    </dl>
                    <p>Kriteria awal adalah kesesuaian jenjang (<em>benefit</em>), kesesuaian mata pelajaran (<em>benefit</em>), dan jarak (<em>cost</em>). Admin dapat mengubahnya, dan sistem menormalkan bobot sebelum menghitung nilai akhir.</p>
                </div>
                <div class="about-calculation-sheet">
                    <div class="about-sheet-heading"><span>CATATAN METODE</span><strong>SAW</strong></div>
                    <dl class="about-equations">
                        <div><dt>Normalisasi benefit</dt><dd><span class="about-equation" role="math" aria-label="r ij sama dengan x ij dibagi nilai maksimum x j">r<sub>ij</sub> = <span class="about-fraction"><span>x<sub>ij</sub></span><span>max(x<sub>j</sub>)</span></span></span></dd></div>
                        <div><dt>Normalisasi cost</dt><dd><span class="about-equation" role="math" aria-label="r ij sama dengan nilai minimum x j dibagi x ij">r<sub>ij</sub> = <span class="about-fraction"><span>min(x<sub>j</sub>)</span><span>x<sub>ij</sub></span></span></span></dd></div>
                        <div><dt>Normalisasi bobot</dt><dd><span class="about-equation" role="math" aria-label="w aksen j sama dengan w j dibagi jumlah seluruh bobot">w&prime;<sub>j</sub> = <span class="about-fraction"><span>w<sub>j</sub></span><span>&Sigma;w</span></span></span></dd></div>
                        <div><dt>Nilai preferensi</dt><dd><span class="about-equation" role="math" aria-label="V i sama dengan jumlah w aksen j dikalikan r ij">V<sub>i</sub> = &Sigma;(w&prime;<sub>j</sub> &times; r<sub>ij</sub>)</span></dd></div>
                    </dl>
                    <p class="about-equation-key"><span><em>x</em> nilai kriteria</span><span><em>r</em> nilai normalisasi</span><span><em>w</em> bobot</span></p>
                </div>
            </div>
            <p class="about-method-conclusion"><i class="bi bi-arrow-up-right" aria-hidden="true"></i><span>Lokasi dengan nilai tertinggi memperoleh ranking tertinggi berdasarkan kriteria dan bobot yang digunakan. Hasil membantu pertimbangan, dan tidak menjamin bahwa suatu lokasi pasti terbaik untuk semua kebutuhan.</span></p>
        </div>
    </section>

    <section class="about-chapter about-data container" aria-labelledby="about-data-title">
        <div class="about-margin"><span class="about-section-number" aria-hidden="true">03</span><span class="about-overline">MEMBACA HASIL</span><p class="about-margin-caption">Kenali apa yang<br>dihitung oleh sistem.</p></div>
        <div class="about-reading">
            <h2 id="about-data-title">Pahami jarak<br><em>dan penilaian.</em></h2>
            <div class="about-data-entry"><span class="about-entry-mark" aria-hidden="true"><i class="bi bi-geo-alt"></i></span><div><h3>Jarak garis lurus</h3><p>Jarak dihitung di backend menggunakan rumus Haversine berdasarkan koordinat sekolah atau lokasi perangkat yang dipilih. Angka ini menunjukkan jarak garis lurus, bukan panjang rute jalan atau estimasi waktu tempuh.</p></div></div>
            <div class="about-data-entry"><span class="about-entry-mark" aria-hidden="true"><i class="bi bi-sliders2"></i></span><div><h3>Kesesuaian tempat belajar</h3><p>Nilai kesesuaian awal menggunakan skala 1&ndash;5 yang dapat diubah admin. Alternatif harus aktif dan memiliki pemetaan jenjang serta mata pelajaran terpilih. Tempat dengan koordinat atau nilai kriteria yang belum lengkap dilewati dan jumlahnya ditampilkan pada hasil.</p></div></div>
            <aside class="about-zero-note" aria-labelledby="about-zero-title"><span class="about-zero-mark" aria-hidden="true">0</span><div><h3 id="about-zero-title">Ketika nilai kriteria adalah nol</h3><p>Jika nilai minimum <em>cost</em> adalah 0, alternatif bernilai 0 mendapat normalisasi 1, sedangkan nilai positif mendapat 0. Jika seluruh nilai <em>benefit</em> bernilai 0, normalisasinya 0. Kebijakan ini mencegah pembagian nol dan ditampilkan pada rincian perhitungan.</p></div></aside>
        </div>
    </section>

    <section class="about-invitation container" aria-labelledby="about-invitation-title">
        <span class="about-invitation-symbol" aria-hidden="true"><i class="bi bi-sun"></i></span>
        <div><span class="about-overline">SAATNYA MENJELAJAH</span><h2 id="about-invitation-title">Dari rasa ingin tahu,<br>ke <em>pengalaman belajar.</em></h2><p>Temukan hubungan antara materi pelajaran dan lingkungan pesisir Bengkulu.</p></div>
        <a class="about-invitation-link" href="{{ route('recommendation.index') }}"><span>Cari <br>Rekomendasi</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </section>
</div>
@endsection
