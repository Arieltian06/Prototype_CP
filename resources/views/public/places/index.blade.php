@extends('layouts.app')
@section('title', 'Jelajahi tempat belajar')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/place-catalog.css').'?v='.filemtime(public_path('css/place-catalog.css')) }}">
@endpush
@section('content')
<div class="catalog-page">
<header class="catalog-header"><div class="container">
    <div class="catalog-edition"><span><i class="bi bi-compass" aria-hidden="true"></i> KOLEKSI RUANG BELAJAR</span><span>PESISIR BENGKULU</span></div>
    <div class="catalog-intro"><h1>Temukan tempat.<br><em>Buka wawasan.</em></h1><div><span class="catalog-cross" aria-hidden="true">+</span><p>Dari ekosistem pantai hingga jejak sejarah. Ada hal baru untuk dipelajari di setiap sudut pesisir Bengkulu.</p><span class="catalog-themes">Alam <span aria-hidden="true">/</span> Sejarah <span aria-hidden="true">/</span> Kehidupan</span></div></div>
</div></header>
<section class="catalog-body"><div class="container">
    <form class="catalog-filters" method="GET" action="{{ route('places.index') }}" role="search" aria-label="Cari tempat belajar">
        <div class="catalog-filter-heading"><span class="catalog-rule" aria-hidden="true"></span><span>Mulai dari rasa ingin tahu</span><i class="bi bi-sliders" aria-hidden="true"></i></div>
        <div class="catalog-filter-fields">
            <div class="filter-search"><label class="form-label" for="search">Cari tempat belajar</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span><input class="form-control" id="search" name="q" value="{{ request('q') }}" maxlength="150" placeholder="Nama tempat, lokasi, atau karakteristik..."></div></div>
            <div class="filter-select"><label class="form-label" for="subject">Mata pelajaran</label><select class="form-select" name="id_mapel" id="subject"><option value="">Semua mata pelajaran</option>@foreach($mapel as $subject)<option value="{{ $subject->id_mapel }}" @selected((string) request('id_mapel') === (string) $subject->id_mapel)>{{ $subject->nama_mapel }} · {{ $subject->jenjang->nama_jenjang }}</option>@endforeach</select></div>
            <div class="catalog-filter-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-sliders" aria-hidden="true"></i> Terapkan</button>@if(request()->filled('q') || request()->filled('id_mapel'))<a class="btn btn-outline-primary" href="{{ route('places.index') }}">Reset</a>@endif</div>
        </div>
    </form>
    <aside class="catalog-side-panel" aria-label="Agenda belajar">
        <div class="catalog-side-heading"><strong>Agenda belajar</strong><i class="bi bi-three-dots" aria-hidden="true"></i></div>
        <div class="catalog-calendar"><div><button type="button" aria-label="Bulan sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><strong>September 2026</strong><button type="button" aria-label="Bulan berikutnya"><i class="bi bi-chevron-right" aria-hidden="true"></i></button></div><ol aria-label="Kalender September 2026"><li>Sen</li><li>Sel</li><li>Rab</li><li>Kam</li><li>Jum</li><li>Sab</li><li>Min</li><li></li><li>1</li><li>2</li><li class="is-active">3</li><li>4</li><li class="is-active">5</li><li>6</li><li>7</li><li>8</li><li>9</li><li>10</li><li>11</li><li class="is-active">12</li><li>13</li><li>14</li><li>15</li><li>16</li><li>17</li><li>18</li><li>19</li><li>20</li><li>21</li><li>22</li><li>23</li><li>24</li><li>25</li><li>26</li><li>27</li><li>28</li><li>29</li><li>30</li></ol></div>
        <div class="catalog-agenda-list"><span>Minggu ini</span><a href="{{ route('recommendation.index') }}"><b>03</b><span><strong>Eksplorasi pesisir</strong><small>Rekomendasi belajar · 6 lokasi</small></span></a><a href="{{ route('places.index') }}"><b>05</b><span><strong>Koleksi tempat</strong><small>Telusuri semua ruang belajar</small></span></a><a href="{{ route('about') }}"><b>12</b><span><strong>Catatan perjalanan</strong><small>Belajar dari lingkungan</small></span></a></div>
    </aside>
    <div class="catalog-results-heading"><h2 id="catalog-results-title"><strong>{{ $places->total() }}</strong> tempat belajar ditemukan @if(request('q'))<span>untuk “{{ request('q') }}”</span>@endif</h2>@if($places->isNotEmpty())<span>URUTAN NAMA <span aria-hidden="true">/</span> A–Z</span>@endif</div>
    <div class="catalog-collection" aria-labelledby="catalog-results-title">
        @forelse($places as $tempat)
            <x-place-card :tempat="$tempat" :sequence="$places->firstItem() + $loop->index"/>
        @empty
            <div class="catalog-empty"><x-empty-state title="Belum ada tempat belajar yang ditemukan." description="Coba kata kunci lain atau lihat semua tempat yang tersedia." icon="search"><a class="btn btn-outline-primary mt-2" href="{{ route('places.index') }}">Lihat semua tempat</a></x-empty-state></div>
        @endforelse
    </div>
    <div class="catalog-pagination">{{ $places->links() }}</div>
</div></section></div>
@endsection
