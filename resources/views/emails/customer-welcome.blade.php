@component('mail::message')
{{-- Ícone de boas-vindas --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #f0fdf4; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTUuNzUgNmEzLjc1IDMuNzUgMCAxIDEtNy41IDAgMy43NSAzLjc1IDAgMCAxIDcuNSAwWk00LjUwMSAyMC4xMThhNy41IDcuNSAwIDAgMSAxNC45OTggMEExNy45MzMgMTcuOTMzIDAgMCAxIDEyIDIxLjc1Yy0yLjY3NiAwLTUuMjE2LS41ODQtNy40OTktMS42MzJaIi8+PC9zdmc+" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Bem-vindo(a)!</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 24px;">Olá, <strong style="color: #18181b;">{{ $customer->display_name }}</strong>! Sua conta foi criada com sucesso.</p>

{{-- Benefícios em cards --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="padding: 16px; background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="padding: 6px 0;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="vertical-align: middle; padding-right: 10px;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOSAxMi43NSAxMS4yNSAxNSAxNSA5Ljc1TTIxIDEyYTkgOSAwIDEgMS0xOCAwIDkgOSAwIDAgMSAxOCAwWiIvPjwvc3ZnPg==" alt="" style="width: 18px; height: 18px; vertical-align: middle;">
</td>
<td style="vertical-align: middle; font-size: 13px; color: #3f3f46;">Acompanhe seus pedidos em tempo real</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding: 6px 0;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="vertical-align: middle; padding-right: 10px;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOSAxMi43NSAxMS4yNSAxNSAxNSA5Ljc1TTIxIDEyYTkgOSAwIDEgMS0xOCAwIDkgOSAwIDAgMSAxOCAwWiIvPjwvc3ZnPg==" alt="" style="width: 18px; height: 18px; vertical-align: middle;">
</td>
<td style="vertical-align: middle; font-size: 13px; color: #3f3f46;">Salve endereços para agilizar suas compras</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding: 6px 0;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="vertical-align: middle; padding-right: 10px;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOSAxMi43NSAxMS4yNSAxNSAxNSA5Ljc1TTIxIDEyYTkgOSAwIDEgMS0xOCAwIDkgOSAwIDAgMSAxOCAwWiIvPjwvc3ZnPg==" alt="" style="width: 18px; height: 18px; vertical-align: middle;">
</td>
<td style="vertical-align: middle; font-size: 13px; color: #3f3f46;">Receba alertas de produtos que voltaram ao estoque</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding: 6px 0;">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="vertical-align: middle; padding-right: 10px;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNOSAxMi43NSAxMS4yNSAxNSAxNSA5Ljc1TTIxIDEyYTkgOSAwIDEgMS0xOCAwIDkgOSAwIDAgMSAxOCAwWiIvPjwvc3ZnPg==" alt="" style="width: 18px; height: 18px; vertical-align: middle;">
</td>
<td style="vertical-align: middle; font-size: 13px; color: #3f3f46;">Acesse seu histórico de compras a qualquer momento</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

@component('mail::button', ['url' => $accountUrl, 'color' => 'primary'])
Acessar Minha Conta
@endcomponent

<p style="text-align: center; color: #a1a1aa; font-size: 12px; margin-top: 8px;">Ou <a href="{{ $storeUrl }}" style="color: #15803d; text-decoration: underline;">continue explorando nossa loja</a></p>
@endcomponent
