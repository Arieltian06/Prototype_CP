@extends('layouts.admin')
@section('title', 'Detail '.$definition['singular'])
@section('content')
<a href="{{ route($definition['route'].'.index') }}" class="admin-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke {{ strtolower($definition['title']) }}</a><div class="admin-page-heading"><div><p class="admin-eyebrow">DETAIL DATA</p><h1>{{ $item->{$definition['name']} }}</h1></div><a class="btn btn-primary" href="{{ route($definition['route'].'.edit', $item->getKey()) }}"><i class="bi bi-pencil me-2" aria-hidden="true"></i> Edit {{ $definition['singular'] }}</a></div>
<section class="admin-card admin-detail-card"><div class="admin-card-body"><dl class="admin-details">@foreach($definition['fields'] as $field)<div><dt>{{ $field['label'] }}</dt><dd>@if($field['type'] === 'jenjang'){{ $item->jenjang?->nama_jenjang ?? '—' }}@elseif($field['type'] === 'select'){{ $field['options'][$item->{$field['key']}] ?? $item->{$field['key']} }}@else{{ $item->{$field['key']} }}@endif</dd></div>@endforeach<div><dt>Terakhir diperbarui</dt><dd>{{ $item->updated_at?->format('d M Y, H:i') }}</dd></div></dl></div></section>
@endsection
