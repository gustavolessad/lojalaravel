@component('mail::message')
{{-- Ícone --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #f0fdf4; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOC4yNSAxOC43NWExLjUgMS41IDAgMSAxLTMgMCAxLjUgMS41IDAgMCAxIDMgMFptMCAwaDZtLTkgMEgzLjM3NWExLjEyNSAxLjEyNSAwIDAgMS0xLjEyNS0xLjEyNVY2LjM3NWMwLS42MjEuNTA0LTEuMTI1IDEuMTI1LTEuMTI1aDkuNzUiLz48cGF0aCBkPSJNMTQuMjUgMTguNzVhMS41IDEuNSAwIDEgMCAzIDAgMS41IDEuNSAwIDAgMC0zIDBabTAgMGgtNm05IDBoMS4xMjVjLjYyMSAwIDEuMTI1LS41MDQgMS4xMjUtMS4xMjV2LTUuMjVjMC0uNjIxLS41MDQtMS4xMjUtMS4xMjUtMS4xMjVoLTMuMzc1bC0yLjI1IDIuMjV2NC4xMjVjMCAuNjIxLjUwNCAxLjEyNSAxLjEyNSAxLjEyNSIvPjxwYXRoIGQ9Ik01LjI1IDExLjI1aDNtLTMgM2gxLjUiLz48L3N2Zz4=" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Pedido enviado!</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 28px;">Olá, <strong style="color: #18181b;">{{ $order->buyer_name }}</strong>! Seu pedido saiu para entrega.</p>

{{-- Informações de envio --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 20px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="text-align: center; padding-bottom: 12px; border-bottom: 1px solid #e4e4e7;">
<span style="font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px;">Pedido</span><br>
<span style="font-size: 18px; font-weight: 700; color: #18181b;">#{{ $order->order_number }}</span>
</td>
</tr>
<tr>
<td style="padding-top: 12px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Transportadora</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">{{ $order->shipping_method }}</td>
</tr>
@if ($order->tracking_code)
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Rastreamento</td>
<td style="font-size: 13px; font-weight: 600; color: #18181b; text-align: right; padding: 4px 0; font-family: monospace;">{{ $order->tracking_code }}</td>
</tr>
@endif
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Prazo estimado</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">até {{ $order->shipping_days }} dias úteis</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

{{-- Endereço --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 16px;">
<p style="font-size: 13px; font-weight: 600; color: #18181b; margin: 0 0 8px;">Endereço de entrega</p>
<p style="font-size: 13px; color: #52525b; margin: 0; line-height: 1.6;">
{{ $order->shipping_street }}, {{ $order->shipping_number }} — {{ $order->shipping_city }}/{{ $order->shipping_state }}
</p>
</td>
</tr>
</table>

@if ($order->tracking_url)
@component('mail::button', ['url' => $url, 'color' => 'primary'])
Rastrear Pedido
@endcomponent
@else
@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver Detalhes do Pedido
@endcomponent
@endif

@if ($order->tracking_code && !$order->tracking_url)
<p style="text-align: center; color: #a1a1aa; font-size: 12px;">Utilize o código <strong>{{ $order->tracking_code }}</strong> no site da transportadora para rastrear.</p>
@endif
@endcomponent
