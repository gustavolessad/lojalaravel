@props(['url'])
@php
    $storeLogo = \App\Models\Setting::get('store_logo', '');
    $storeName = \App\Models\Setting::get('store_name', config('app.name'));
    $logoUrl = $storeLogo ? \Illuminate\Support\Facades\Storage::disk('public')->url($storeLogo) : null;
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $storeName }}">
@else
{{ $storeName }}
@endif
</a>
</td>
</tr>
