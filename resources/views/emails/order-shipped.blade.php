@component('mail::message')
# Seu pedido foi enviado!

Olá, **{{ $order->buyer_name }}**!

Seu **Pedido #{{ $order->order_number }}** saiu para entrega!

@component('mail::panel')
**Transportadora:** {{ $order->shipping_method }}
@if ($order->tracking_code)
**Código de rastreamento:** `{{ $order->tracking_code }}`
@endif
**Prazo estimado:** até {{ $order->shipping_days }} dias úteis
**Endereço de entrega:** {{ $order->shipping_street }}, {{ $order->shipping_number }} — {{ $order->shipping_city }}/{{ $order->shipping_state }}
@endcomponent

@if ($order->tracking_url)
@component('mail::button', ['url' => $url, 'color' => 'primary'])
Rastrear Pedido
@endcomponent
@elseif ($order->tracking_code)
Utilize o código **{{ $order->tracking_code }}** no site da transportadora para rastrear sua encomenda.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver Detalhes do Pedido
@endcomponent
@else
@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver Detalhes do Pedido
@endcomponent
@endif

Obrigado pela sua compra,
{{ config('app.name') }}
@endcomponent
