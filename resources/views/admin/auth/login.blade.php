<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
    <title>Login Admin · EduPesisir Bengkulu</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}"><link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}"><link rel="stylesheet" href="{{ asset('css/edupesisir.css').'?v='.filemtime(public_path('css/edupesisir.css')) }}"><link rel="stylesheet" href="{{ asset('css/admin.css').'?v='.filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin-body login-body">
<main class="login-shell">
    <section class="login-story"><a href="{{ url('/') }}" class="admin-brand"><span class="admin-brand-icon"><i class="bi bi-water" aria-hidden="true"></i></span><span>Edu<span class="brand-accent">Pesisir</span><small>BENGKULU</small></span></a><div class="login-story-content"><span class="login-pill"><i class="bi bi-mortarboard" aria-hidden="true"></i> Pendidikan bertemu pesisir</span><h1>Kelola informasi.<br>Buka ruang<br><span>belajar baru.</span></h1><p>Hadirkan pengalaman belajar luar kelas yang relevan melalui data tempat, pembelajaran, dan rekomendasi yang transparan.</p><div class="login-features"><span><i class="bi bi-compass" aria-hidden="true"></i> Tempat edukatif</span><span><i class="bi bi-calculator" aria-hidden="true"></i> Metode SAW</span></div></div><p class="login-story-footer">Platform pembelajaran kontekstual pesisir Kota Bengkulu.</p></section>
    <section class="login-form-panel"><a href="{{ url('/') }}" class="admin-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke beranda</a><div class="login-form-wrap"><span class="login-lock"><i class="bi bi-shield-lock" aria-hidden="true"></i></span><p class="admin-eyebrow">RUANG ADMINISTRATOR</p><h2>Selamat datang kembali</h2><p class="text-muted mb-4">Masuk untuk mengelola EduPesisir Bengkulu.</p>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.login.store') }}">@csrf
        <div class="mb-4"><label class="form-label" for="email">Email admin</label><input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ is_scalar(old('email')) ? old('email') : '' }}" required maxlength="255" autocomplete="username" autofocus placeholder="nama@edupesisir.test"></div>
        <div class="mb-4"><label class="form-label" for="password">Kata sandi</label><div class="input-group"><input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required maxlength="255" autocomplete="current-password" placeholder="Masukkan kata sandi"><button class="btn btn-outline-secondary" type="button" id="toggle-password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
        <button class="btn btn-primary w-100 login-submit" type="submit">Masuk ke Dashboard <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button>
    </form><p class="login-access-note"><i class="bi bi-lock" aria-hidden="true"></i> Akses ini khusus administrator sistem.</p></div><p class="login-footer">Capstone Project · Universitas Bengkulu · {{ date('Y') }}</p></section>
</main>
<script>document.getElementById('toggle-password').addEventListener('click', function () { const field = document.getElementById('password'); const shown = field.type === 'password'; field.type = shown ? 'text' : 'password'; this.setAttribute('aria-pressed', String(shown)); this.setAttribute('aria-label', shown ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'); this.firstElementChild.className = shown ? 'bi bi-eye-slash' : 'bi bi-eye'; });</script>
</body>
</html>
