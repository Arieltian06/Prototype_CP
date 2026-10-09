@props(['title' => 'Belum ada data yang tersedia.', 'description' => 'Silakan coba kembali nanti atau ubah pencarian Anda.', 'icon' => 'compass'])
<div class="empty-state"><span class="empty-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><h2>{{ $title }}</h2><p>{{ $description }}</p>{{ $slot }}</div>
