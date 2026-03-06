@component('mail::message')
{{-- Ícone --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #f0fdf4; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTUuNzUgMTAuNVY2YTMuNzUgMy43NSAwIDEgMC03LjUgMHY0LjVtMTEuMzU2LTEuOTkzIDEuMjYzIDEyYy4wNy42NjUtLjQ1IDEuMjQzLTEuMTE5IDEuMjQzSDQuMjVhMS4xMjUgMS4xMjUgMCAwIDEtMS4xMi0xLjI0M2wxLjI2NC0xMkExLjEyNSAxLjEyNSAwIDAgMSA1LjUxMyA3LjVoMTIuOTc0Yy41NzYgMCAxLjA1OS40MzUgMS4xMTkgMS4wMDdaIi8+PC9zdmc+" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Pedido recebido!</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 28px;">Olá, <strong style="color: #18181b;">{{ $order->buyer_name }}</strong>! Recebemos seu pedido. Assim que o pagamento for confirmado, começaremos a prepará-lo.</p>

{{-- Número do pedido --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 16px; text-align: center;">
<span style="font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px;">Pedido</span><br>
<span style="font-size: 18px; font-weight: 700; color: #18181b;">#{{ $order->order_number }}</span>
</td>
</tr>
</table>

{{-- Itens --}}
@component('mail::table')
| Produto | Qtd | Subtotal |
|---------|:---:|---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_label ? ' — ' . $item->variant_label : '' }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->total, 2, ',', '.') }} |
@endforeach
@endcomponent

{{-- Resumo financeiro --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 16px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Subtotal</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</td>
</tr>
@if ((float) $order->discount > 0)
<tr>
<td style="font-size: 13px; color: #15803d; padding: 4px 0;">Cupom ({{ $order->coupon_code }})</td>
<td style="font-size: 13px; color: #15803d; text-align: right; padding: 4px 0;">- R$ {{ number_format((float) $order->discount, 2, ',', '.') }}</td>
</tr>
@endif
@if ((float) $order->pix_discount > 0)
<tr>
<td style="font-size: 13px; color: #15803d; padding: 4px 0;">Desconto PIX</td>
<td style="font-size: 13px; color: #15803d; text-align: right; padding: 4px 0;">- R$ {{ number_format((float) $order->pix_discount, 2, ',', '.') }}</td>
</tr>
@endif
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Frete ({{ $order->shipping_method }})</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">R$ {{ number_format((float) $order->shipping_cost, 2, ',', '.') }}</td>
</tr>
<tr><td colspan="2" style="border-top: 1px solid #e4e4e7; padding-top: 8px;"></td></tr>
<tr>
<td style="font-size: 15px; font-weight: 700; color: #18181b; padding: 4px 0;">Total</td>
<td style="font-size: 15px; font-weight: 700; color: #18181b; text-align: right; padding: 4px 0;">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td>
</tr>
<tr>
<td colspan="2" style="font-size: 12px; color: #71717a; padding-top: 4px;">{{ $order->payment_method_label }}</td>
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
{{ $order->shipping_name }}<br>
{{ $order->shipping_street }}, {{ $order->shipping_number }}{{ $order->shipping_complement ? ' — ' . $order->shipping_complement : '' }}<br>
{{ $order->shipping_district }}, {{ $order->shipping_city }}/{{ $order->shipping_state }} — CEP {{ $order->shipping_zip }}
</p>
<p style="font-size: 12px; color: #71717a; margin: 8px 0 0;">Prazo estimado: até {{ $order->shipping_days }} dias úteis após confirmação do pagamento</p>
</td>
</tr>
</table>

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver Detalhes do Pedido
@endcomponent
@endcomponent
