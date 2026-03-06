@php
    $storeName        = \App\Models\Setting::get('store_name', config('app.name'));
    $storeRazaoSocial = \App\Models\Setting::get('store_razao_social', '');
    $storeCnpj        = \App\Models\Setting::get('store_cpf_cnpj', '');
    $storeAddress     = \App\Models\Setting::get('store_address', '');
    $storeHours       = \App\Models\Setting::get('store_hours', '');
    $storePhone       = \App\Models\Setting::get('store_phone', '');
    $storeEmail       = \App\Models\Setting::get('store_email', '');
@endphp
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 32px 32px 24px;">

@if ($storeRazaoSocial || $storeCnpj)
<p style="color: #52525b; font-size: 12px; text-align: center; margin: 0 0 4px; line-height: 1.7; font-weight: 600;">
{{ $storeRazaoSocial }}@if ($storeRazaoSocial && $storeCnpj) &nbsp;&middot;&nbsp; @endif@if ($storeCnpj)CNPJ: {{ $storeCnpj }}@endif
</p>
@endif

@if ($storeAddress)
<p style="color: #71717a; font-size: 12px; text-align: center; margin: 0 0 4px; line-height: 1.7;">
{{ $storeAddress }}
</p>
@endif

@if ($storeHours || $storePhone || $storeEmail)
<p style="color: #71717a; font-size: 12px; text-align: center; margin: 0 0 4px; line-height: 1.7;">
@if ($storeHours)Atendimento: {{ $storeHours }}@endif
@if ($storeHours && ($storePhone || $storeEmail)) &nbsp;&middot;&nbsp; @endif
@if ($storePhone){{ $storePhone }}@endif
@if ($storePhone && $storeEmail) &nbsp;&middot;&nbsp; @endif
@if ($storeEmail){{ $storeEmail }}@endif
</p>
@endif

<p style="color: #71717a; font-size: 12px; text-align: center; margin: 14px 0 0; line-height: 1.7;">
&copy; {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.
</p>

{{-- Créditos Targos --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center" style="padding-top: 20px;">
<table cellpadding="0" cellspacing="0" role="presentation" style="border-top: 1px solid #e4e4e7; width: 200px;">
<tr>
<td align="center" style="padding-top: 16px; padding-bottom: 8px;">
<p style="color: #a1a1aa; font-size: 10px; margin: 0 0 6px; text-align: center;">
Plataforma de Loja Virtual
</p>
<a href="https://targos.com.br" target="_blank" rel="noopener" style="display: inline-block;">
<img src="{{ asset('images/logo-targos.png') }}" alt="Targos" style="height: 14px; max-height: 14px; width: auto;">
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>

</td>
</tr>
</table>
</td>
</tr>
