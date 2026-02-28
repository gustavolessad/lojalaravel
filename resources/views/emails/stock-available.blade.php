@component('mail::message')
# Boas notícias!

Olá!

O produto que você estava aguardando **voltou ao estoque**!

@component('mail::panel')
**{{ $product?->name ?? 'Produto' }}**
@if ($variant)
Variação: {{ $variant->label }}
@endif

Corra, pois o estoque pode ser limitado!
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Comprar Agora
@endcomponent

Este e-mail foi enviado porque você se cadastrou para ser avisado sobre a disponibilidade deste produto.

Até logo,
{{ config('app.name') }}
@endcomponent
