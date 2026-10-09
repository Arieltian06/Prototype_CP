@extends('layouts.app')
@section('title', $tempat->nama_tempat)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/place-detail.css').'?v='.filemtime(public_path('css/place-detail.css')) }}">
@endpush
@section('content')
@php
    $mapData = ['places' => [[
        'id' => $tempat->id_tempat,
        'latitude' => $tempat->latitude,
        'longitude' => $tempat->longitude,
        'nama' => $tempat->nama_tempat,
        'url' => route('places.show', $tempat),
    ]]];
    $activities = array_filter(array_map('trim', preg_split('/[;\r\n]+/u', $tempat->aktivitas ?? '')));
@endphp
<div class="place-detail-page"><div class="container">
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item"><a href="{{ route('places.index') }}">Tempat Belajar</a></li><li class="breadcrumb-item active" aria-current="page">{{ $tempat->nama_tempat }}</li></ol></nav>
    <header class="place-detail-heading">
        <div><span class="eyebrow">KENALI RUANG BELAJAR</span><h1>{{ $tempat->nama_tempat }}</h1><p class="place-detail-address"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ \App\Support\PublicContent::text($tempat->alamat) }}</span></p></div>
        <div class="place-detail-character"><span>KARAKTER TEMPAT</span><p>{{ \App\Support\PublicContent::text($tempat->karakteristik) }}</p></div>
    </header>
    <div class="place-detail-hero">
        <figure class="place-detail-photo"><img src="{{ $tempat->foto_url }}" alt="Visual {{ $tempat->nama_tempat }}" width="900" height="600" fetchpriority="high"><figcaption><span>PESISIR BENGKULU / RUANG BELAJAR</span><i class="bi bi-compass" aria-hidden="true"></i></figcaption></figure>
        <aside class="place-visit-sheet" aria-labelledby="place-visit-title">
            <div class="place-visit-sheet-top"><span>INFORMASI KUNJUNGAN</span><i class="bi bi-signpost-split" aria-hidden="true"></i></div>
            <h2 id="place-visit-title">Sebelum<br>mulai menjelajah.</h2>
            <dl class="place-visit-data"><div><dt><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Estimasi biaya</dt><dd>{{ (float)$tempat->biaya === 0.0 ? 'Tanpa biaya' : 'Rp '.number_format((float)$tempat->biaya,0,',','.') }}</dd></div><div><dt><i class="bi bi-clock" aria-hidden="true"></i> Jam operasional</dt><dd>{{ \App\Support\PublicContent::text($tempat->jam_operasional) ?: 'Belum tersedia' }}</dd></div><div><dt>Status tempat</dt><dd>Aktif</dd></div></dl>
            <p>Cari tahu kesesuaian tempat ini dengan jenjang, mata pelajaran, dan titik berangkatmu.</p>
            <a class="btn" href="{{ route('recommendation.index') }}">Cari Rekomendasi Terkait <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </aside>
    </div>
    <div class="place-detail-reading">
        <div>
            <section class="place-story" aria-labelledby="place-story-title"><div class="place-story-title"><span aria-hidden="true">01</span><h2 id="place-story-title">Tentang tempat belajar</h2></div><p>{{ \App\Support\PublicContent::text($tempat->deskripsi) }}</p></section>
            <section class="place-story" aria-labelledby="place-learning-title"><div class="place-story-title"><span aria-hidden="true">02</span><h2 id="place-learning-title">Belajar apa di sini?</h2></div><ol class="field-observation-list">@forelse($activities as $activity)<li>{{ \App\Support\PublicContent::text($activity) }}</li>@empty<li>Aktivitas pembelajaran belum ditambahkan.</li>@endforelse</ol>
                <div class="place-subject-sheet"><h3>TERHUBUNG DENGAN PELAJARANMU</h3><div class="subject-tags">@forelse($tempat->mapel->pluck('nama_mapel')->unique() as $subject)<span>{{ $subject }}</span>@empty<span>Mata pelajaran belum ditambahkan</span>@endforelse</div>@if($tempat->jenjang->isNotEmpty())<p>Jenjang terkait: <strong>{{ $tempat->jenjang->pluck('nama_jenjang')->implode(', ') }}</strong>.</p>@endif</div>
            </section>
        </div>
        <aside class="place-map-sheet" aria-labelledby="place-map-title">
            <div class="place-map-title"><div><span>POSISI DI PESISIR</span><h2 id="place-map-title">Lokasi tempat</h2></div><i class="bi bi-compass" aria-hidden="true"></i></div>
            @if($tempat->latitude !== null && $tempat->longitude !== null)
                <div class="map-frame" id="place-map" data-map data-map-config="place-map-data" aria-label="Peta lokasi {{ $tempat->nama_tempat }}"><div class="map-placeholder">Memuat peta lokasi...</div></div>
                <script type="application/json" id="place-map-data">@json($mapData)</script>
                <p class="map-caption">{{ $tempat->latitude }}, {{ $tempat->longitude }}<br>Peta memerlukan koneksi internet.</p>
            @else
                <div class="map-frame"><div class="map-placeholder">Koordinat tempat belum tersedia.</div></div>
            @endif
            <a class="place-return-link" href="{{ route('places.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Jelajahi tempat lainnya</a>
        </aside>
    </div>
</div></div>
@endsection
