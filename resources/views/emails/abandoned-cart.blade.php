@component('mail::message')
# Você esqueceu algo!

Olá, **{{ $customer->display_name }}**!

Notamos que você deixou {{ $cart->item_count }} {{ $cart->item_count === 1 ? 'item' : 'itens' }} no seu carrinho. Ainda dá tempo de finalizar sua compra!

@component('mail::table')
| Produto | Qtd | Preço |
|---------|:---:|------:|
@foreach ($cart->items as $item)
| {{ $item->name }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }} |
@endforeach
@endcomponent

**Total:** R$ {{ number_format($cart->subtotal, 2, ',', '.') }}

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Finalizar Compra
@endcomponent

Os itens do seu carrinho estão reservados por tempo limitado. Não deixe para depois!

Até logo,
{{ config('app.name') }}

@component('mail::subcopy')
Se você não deseja mais receber este tipo de e-mail, [clique aqui para cancelar]({{ url('/') }}).
@endcomponent
@endcomponent
