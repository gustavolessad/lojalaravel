@component('mail::message')
{{-- Ícone --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #f0fdf4; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOSAxMi43NSAxMS4yNSAxNSAxNSA5Ljc1TTIxIDEyYTkgOSAwIDEgMS0xOCAwIDkgOSAwIDAgMSAxOCAwWiIvPjwvc3ZnPg==" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Pagamento confirmado!</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 28px;">Olá, <strong style="color: #18181b;">{{ $order->buyer_name }}</strong>! Ótima notícia: o pagamento do seu pedido foi confirmado.</p>

{{-- Detalhes do pagamento --}}
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
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Valor pago</td>
<td style="font-size: 13px; font-weight: 600; color: #18181b; text-align: right; padding: 4px 0;">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td>
</tr>
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Forma de pagamento</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">{{ $order->payment_method_label }}</td>
</tr>
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Confirmado em</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">{{ $order->paid_at?->format('d/m/Y \à\s H:i') ?? now()->format('d/m/Y \à\s H:i') }}</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

<p style="text-align: center; color: #52525b; font-size: 14px;">Seu pedido agora está sendo <strong>preparado para envio</strong>. Você receberá outro e-mail assim que ele for despachado.</p>

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Acompanhar Pedido
@endcomponent
@endcomponent
