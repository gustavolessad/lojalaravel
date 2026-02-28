@component('mail::message')
# Pedido recebido!

Olá, **{{ $order->buyer_name }}**!

Recebemos seu pedido com sucesso. Assim que o pagamento for confirmado, começaremos a prepará-lo para envio.

---

## Resumo do Pedido #{{ $order->order_number }}

@component('mail::table')
| Produto | Qtd | Preço Unit. | Subtotal |
|---------|:---:|------------:|---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant_label ? ' — ' . $item->variant_label : '' }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }} | R$ {{ number_format((float) $item->total, 2, ',', '.') }} |
@endforeach
@endcomponent

| | |
|-|-:|
| Subtotal | R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }} |
@if ((float) $order->discount > 0)
| Desconto ({{ $order->coupon_code }}) | − R$ {{ number_format((float) $order->discount, 2, ',', '.') }} |
@endif
@if ((float) $order->pix_discount > 0)
| Desconto PIX | − R$ {{ number_format((float) $order->pix_discount, 2, ',', '.') }} |
@endif
| Frete ({{ $order->shipping_method }}) | R$ {{ number_format((float) $order->shipping_cost, 2, ',', '.') }} |
| **Total** | **R$ {{ number_format((float) $order->total, 2, ',', '.') }}** |

**Forma de pagamento:** {{ $order->payment_method_label }}

---

## Endereço de Entrega

{{ $order->shipping_name }}
{{ $order->shipping_street }}, {{ $order->shipping_number }}{{ $order->shipping_complement ? ' — ' . $order->shipping_complement : '' }}
{{ $order->shipping_district }}, {{ $order->shipping_city }}/{{ $order->shipping_state }}
CEP {{ $order->shipping_zip }}

**Prazo estimado:** até {{ $order->shipping_days }} dias úteis após confirmação do pagamento.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver Detalhes do Pedido
@endcomponent

Qualquer dúvida, estamos à disposição!

Obrigado pela sua compra,
{{ config('app.name') }}
@endcomponent
