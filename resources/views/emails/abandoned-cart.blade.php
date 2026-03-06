@component('mail::message')
{{-- Ícone --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #fef3c7; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNkOTc3MDYiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMi4yNSAzaDEuMzg2Yy41MS4wLjk1NS4zNDMgMS4wODcuODM1bC4zODMgMS40MzdNNy41IDE0LjI1YTMgMyAwIDAgMC01LjkgOGg1LjlabTAgMGg2bTYgMEg4Ljc1bS0uMDE2IDBoLjAxNm0tLjAxNiAwaC4wMTZNMTUgMTEuMjVsLTMtM20wIDBsLTMgM20zLTN2OS43NSIvPjwvc3ZnPg==" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Esqueceu algo?</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 28px;">Olá, <strong style="color: #18181b;">{{ $customer->display_name }}</strong>! Você deixou {{ $cart->item_count }} {{ $cart->item_count === 1 ? 'item' : 'itens' }} no seu carrinho.</p>

{{-- Itens --}}
@component('mail::table')
| Produto | Qtd | Preço |
|---------|:---:|------:|
@foreach ($cart->items as $item)
| {{ $item->name }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }} |
@endforeach
@endcomponent

{{-- Total --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 16px; text-align: center;">
<span style="font-size: 12px; color: #71717a;">Total do carrinho</span><br>
<span style="font-size: 18px; font-weight: 700; color: #18181b;">R$ {{ number_format($cart->subtotal, 2, ',', '.') }}</span>
</td>
</tr>
</table>

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Finalizar Compra
@endcomponent

<p style="text-align: center; color: #a1a1aa; font-size: 12px;">Os itens do seu carrinho estão reservados por tempo limitado.</p>

@component('mail::subcopy')
Se você não deseja mais receber este tipo de e-mail, <a href="{{ url('/') }}">clique aqui para cancelar</a>.
@endcomponent
@endcomponent
