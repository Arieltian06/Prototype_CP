<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · Admin EduPesisir</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/edupesisir.css').'?v='.filemtime(public_path('css/edupesisir.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css').'?v='.filemtime(public_path('css/admin.css')) }}">
    @stack('styles')
</head>
<body class="admin-body">
<a class="visually-hidden-focusable admin-skip" href="#admin-main">Langsung ke konten</a>
<div class="admin-overlay" id="admin-overlay" hidden></div>
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Navigasi admin">
    <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-icon"><i class="bi bi-water" aria-hidden="true"></i></span><span>Edu<span class="brand-accent">Pesisir</span><small>PANEL ADMINISTRATOR</small></span></a>
    <div class="sidebar-label">RUANG PENGELOLAAN</div>
    <nav class="admin-nav">
        @foreach([
            ['admin.dashboard','grid-1x2','Dashboard','admin.dashboard'],
            ['admin.tempat.index','geo-alt','Tempat Belajar','admin.tempat.*'],
            ['admin.jenjang.index','mortarboard','Jenjang Pendidikan','admin.jenjang.*'],
            ['admin.mapel.index','book','Mata Pelajaran','admin.mapel.*'],
            ['admin.sekolah.index','buildings','Sekolah','admin.sekolah.*'],
            ['admin.kriteria.index','sliders','Kriteria & Bobot SAW','admin.kriteria.*'],
            ['admin.kesesuaian.index','clipboard-check','Nilai Kesesuaian','admin.kesesuaian.*|admin.skala.*'],
            ['admin.saw.index','calculator','Perhitungan SAW','admin.saw.*']
        ] as [$route, $icon, $label, $pattern])
            <a href="{{ route($route) }}" @class(['active' => request()->routeIs(...explode('|', $pattern))]) @if(request()->routeIs(...explode('|', $pattern))) aria-current="page" @endif><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span></a>
        @endforeach
    </nav>
    <div class="sidebar-bottom"><div class="sidebar-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Ruang kelola data & parameter rekomendasi pembelajaran pesisir.</span></div><a href="{{ url('/') }}"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Lihat website</a><form action="{{ route('admin.logout') }}" method="post">@csrf<button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar</button></form></div>
</aside>
<div class="admin-workspace">
    <header class="admin-topbar"><div class="d-flex align-items-center gap-3"><button class="admin-menu-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Buka navigasi" id="admin-menu-toggle"><i class="bi bi-list" aria-hidden="true"></i></button><div><span class="admin-topbar-eyebrow">EDUPESISIR BENGKULU</span><span class="admin-topbar-title">@yield('title', 'Dashboard')</span></div></div><div class="admin-profile"><span class="admin-avatar">{{ mb_strtoupper(mb_substr(auth('admin')->user()->nama, 0, 1)) }}</span><div><strong>{{ auth('admin')->user()->nama }}</strong><small>Administrator</small></div></div></header>
    <main class="admin-main" id="admin-main">
        @if(session('success'))<div class="alert alert-success d-flex align-items-start gap-2" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><span>{{ session('success') }}</span></div>@endif
        @if(session('error'))<div class="alert alert-danger d-flex align-items-start gap-2" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i><span>{{ session('error') }}</span></div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Periksa kembali isian Anda.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
    <footer class="admin-footer"><span>© {{ date('Y') }} EduPesisir Bengkulu</span><span>Prototype Capstone · Informatika UNIB</span></footer>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('js/edupesisir.js') }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('admin-menu-toggle');
    const overlay = document.getElementById('admin-overlay');
    const sidebar = document.getElementById('admin-sidebar');
    function toggle(open) { document.body.classList.toggle('sidebar-open', open); button.setAttribute('aria-expanded', String(open)); overlay.hidden = !open; if (open) sidebar.querySelector('a').focus(); }
    button.addEventListener('click', () => toggle(!document.body.classList.contains('sidebar-open')));
    overlay.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) { toggle(false); button.focus(); } });
});
</script>
@stack('scripts')
</body>
</html>
