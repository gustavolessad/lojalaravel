@component('mail::message')
# Pagamento confirmado!

Olá, **{{ $order->buyer_name }}**!

Ótima notícia: seu pagamento do **Pedido #{{ $order->order_number }}** foi confirmado.

@component('mail::panel')
**Valor pago:** R$ {{ number_format((float) $order->total, 2, ',', '.') }}
**Forma de pagamento:** {{ $order->payment_method_label }}
**Pago em:** {{ $order->paid_at?->format('d/m/Y \à\s H:i') ?? now()->format('d/m/Y \à\s H:i') }}
@endcomponent

Seu pedido agora está sendo **preparado para envio**. Você receberá outro e-mail assim que ele for despachado com o código de rastreamento.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Acompanhar Pedido
@endcomponent

Obrigado pela sua compra,
{{ config('app.name') }}
@endcomponent
