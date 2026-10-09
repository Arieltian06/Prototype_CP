<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173e43">
    <meta name="description" content="Temukan ruang belajar di pesisir Bengkulu dan rekomendasi tempat yang sesuai dengan jenjang, mata pelajaran, dan titik berangkatmu.">
    <title>@yield('title', 'Ruang belajar di pesisir') &middot; EduPesisir Bengkulu</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="preload" href="{{ asset('fonts/InterVariable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    @php($assetVersion = static function (string $path): string { return is_file($path) ? (string) filemtime($path) : '1'; })
    <link rel="stylesheet" href="{{ asset('css/edupesisir.css').'?v='.$assetVersion(public_path('css/edupesisir.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/atlas.css').'?v='.$assetVersion(public_path('css/atlas.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-pages.css').'?v='.$assetVersion(public_path('css/public-pages.css')) }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/public-dashboard.css').'?v='.$assetVersion(public_path('css/public-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/oceanic-edtech.css').'?v='.$assetVersion(public_path('css/oceanic-edtech.css')) }}">
</head>
<body class="atlas-site dashboard-body oceanic-ui">
<a class="skip-link" href="#main-content">Lewati ke konten utama</a>
<div class="dashboard-shell">
    <div class="dashboard-overlay" id="dashboard-overlay" hidden></div>
    <aside class="dashboard-sidebar" id="dashboard-sidebar" aria-label="Navigasi utama">
        <a class="dashboard-brand" href="{{ route('home') }}" aria-label="EduPesisir, beranda"><span class="dashboard-brand-mark"><i class="bi bi-compass" aria-hidden="true"></i></span><span>Edu<span>Pesisir</span><small>BENGKULU</small></span></a>
        <span class="dashboard-section-label">MAIN MENU</span>
        <nav class="dashboard-nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span>Dashboard</span></a>
            <a href="{{ route('places.index') }}" class="{{ request()->routeIs('places.*') ? 'active' : '' }}"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Tempat Belajar</span></a>
            <a href="{{ route('recommendation.index') }}" class="{{ request()->routeIs('recommendation.*') ? 'active' : '' }}"><i class="bi bi-stars" aria-hidden="true"></i><span>Rekomendasi</span></a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}"><i class="bi bi-compass" aria-hidden="true"></i><span>Tentang EduPesisir</span></a>
        </nav>
        <div class="dashboard-sidebar-card"><i class="bi bi-lightbulb" aria-hidden="true"></i><strong>Mulai menjelajah</strong><p>Temukan pelajaran baru dari pesisir Bengkulu.</p><a href="{{ route('recommendation.index') }}">Cari rekomendasi <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="dashboard-sidebar-bottom"><a href="{{ route('admin.login') }}"><i class="bi bi-shield-lock" aria-hidden="true"></i> Masuk admin</a><span>&copy; {{ date('Y') }} EduPesisir</span></div>
    </aside>
    <div class="dashboard-workspace">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar-start"><button class="dashboard-menu-toggle" type="button" aria-controls="dashboard-sidebar" aria-expanded="false" aria-label="Buka navigasi" id="dashboard-menu-toggle"><i class="bi bi-list" aria-hidden="true"></i></button><div><span class="dashboard-eyebrow">RUANG BELAJAR PESISIR</span><strong>@yield('title', 'Dashboard')</strong></div></div>
            <div class="dashboard-topbar-actions"><form class="dashboard-global-search" method="get" action="{{ route('places.index') }}" role="search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari tempat belajar..."><button type="submit" aria-label="Cari"><i class="bi bi-arrow-right" aria-hidden="true"></i></button></form><button class="dashboard-icon-button" type="button" aria-label="Notifikasi"><i class="bi bi-bell" aria-hidden="true"></i><span></span></button><a class="dashboard-profile" href="{{ route('about') }}"><span class="dashboard-avatar">E</span><span><strong>Eksplorator</strong><small>Pengunjung</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></a></div>
        </header>
        <main id="main-content" class="dashboard-main">
    @if(session('success'))<div class="container pt-4"><div class="alert alert-success" role="status"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="container pt-4"><div class="alert alert-danger" role="alert">{{ session('error') }}</div></div>@endif
    @if($errors->any())<div class="container pt-4"><div class="alert alert-danger" role="alert"><strong>Periksa kembali isian Anda.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    @yield('content')
        </main>
        <footer class="dashboard-footer"><span>&copy; {{ date('Y') }} EduPesisir Bengkulu</span><span>Dari pesisir, untuk rasa ingin tahu.</span></footer>
    </div>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>
<script src="{{ asset('js/edupesisir.js').'?v='.$assetVersion(public_path('js/edupesisir.js')) }}" defer></script>
@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('dashboard-menu-toggle');
    const overlay = document.getElementById('dashboard-overlay');
    const sidebar = document.getElementById('dashboard-sidebar');
    if (!button || !overlay || !sidebar) return;
    function toggle(open) { document.body.classList.toggle('dashboard-sidebar-open', open); button.setAttribute('aria-expanded', String(open)); overlay.hidden = !open; if (open) sidebar.querySelector('a').focus(); }
    button.addEventListener('click', () => toggle(!document.body.classList.contains('dashboard-sidebar-open')));
    overlay.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && document.body.classList.contains('dashboard-sidebar-open')) { toggle(false); button.focus(); } });
});
</script>
</body>
</html>
