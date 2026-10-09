@props(['tempat', 'sequence' => null])
<article class="catalog-card">
    <div class="catalog-card-masthead"><span>@if($sequence !== null)<span class="catalog-card-number" aria-hidden="true">{{ str_pad((string) $sequence, 2, '0', STR_PAD_LEFT) }}</span>@endif RUANG BELAJAR</span><i class="bi bi-compass" aria-hidden="true"></i></div>
    <a class="catalog-card-image" href="{{ route('places.show', $tempat) }}" tabindex="-1" aria-hidden="true"><img src="{{ $tempat->foto_url }}" alt="" loading="lazy" width="640" height="400"><span class="catalog-image-caption"><i class="bi bi-geo-alt" aria-hidden="true"></i> PESISIR BENGKULU</span></a>
    <div class="catalog-card-copy">
        <span class="catalog-category">{{ \App\Support\PublicContent::text($tempat->karakteristik) }}</span>
        <h3><a href="{{ route('places.show', $tempat) }}">{{ $tempat->nama_tempat }}</a></h3>
        <p class="catalog-description">{{ \Illuminate\Support\Str::limit(\App\Support\PublicContent::text($tempat->deskripsi), 155) }}</p>
        <p class="catalog-address"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ \App\Support\PublicContent::text($tempat->alamat) }}</span></p>
        <div class="subject-tags">@foreach($tempat->mapel->pluck('nama_mapel')->unique()->take(3) as $subject)<span>{{ $subject }}</span>@endforeach @if($tempat->mapel->pluck('nama_mapel')->unique()->count() > 3)<span>+{{ $tempat->mapel->pluck('nama_mapel')->unique()->count() - 3 }}</span>@endif</div>
    </div>
    <div class="catalog-card-footer"><span><small>BIAYA KUNJUNGAN</small><strong>{{ (float)$tempat->biaya === 0.0 ? 'Tanpa biaya' : 'Rp '.number_format((float)$tempat->biaya, 0, ',', '.') }}</strong></span><a href="{{ route('places.show', $tempat) }}" aria-label="Lihat detail {{ $tempat->nama_tempat }}">Kenali tempat <span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span></a></div>
</article>
