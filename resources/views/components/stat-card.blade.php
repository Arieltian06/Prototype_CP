@props(['label', 'value', 'icon' => 'bar-chart'])
<div class="stat-card"><span class="stat-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><div><strong>{{ $value }}</strong><span>{{ $label }}</span></div></div>
