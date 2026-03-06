@component('mail::message')
{{-- Ícone --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #f0fdf4; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTQuODU3IDcuMTQzYTguOTYzIDguOTYzIDAgMCAwLTIuODU3LS40NjhjLTUuMjg1IDAtOS41NjYgMy43Mi05LjU2NiA4LjMxNSAwIDEuNjk2LjQ5IDMuMjUgMS4zMTQgNC41NjRMLjA3NSAxMy45NTRhMSAxIDAgMCAxIC43ODUtMS4yMDdsMS41NTMtLjMxYTguMTkzIDguMTkzIDAgMCAxLS4xNjUtMS42NjJjMC00LjU5NiA0LjI4MS04LjMxNiA5LjU2Ni04LjMxNiAyLjYzNSAwIDUuMDE5IDEuMDE2IDYuNzggMi42NTFsLTMuNTM3IDIuMDMzWiIvPjxwYXRoIGQ9Ik0xNC44NTcgMTcuMDgyYTguOTYzIDguOTYzIDAgMCAxLTIuODU3LjQ2OGMtMS4wNyAwLTIuMDktLjE4LTMuMDMyLS41MTJsLTMuODkzLjc3OGExIDEgMCAwIDEtMS4xNS0xLjM1OGwxLjI2OC00LjA1NWE4LjU0NCA4LjU0NCAwIDAgMS0uMjk4LTIuMTg0bDIuMzQzIDEuMzQ2YTYuMTcgNi4xNyAwIDAgMC0uMjMuNzAzIi8+PHBhdGggZD0iTTkgMTIuNzUgMTEuMjUgMTUgMTUgOS43NSIvPjwvc3ZnPg==" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Voltou ao estoque!</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 28px;">O produto que você estava aguardando está disponível novamente.</p>

{{-- Produto --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 20px; text-align: center;">
<span style="font-size: 15px; font-weight: 700; color: #18181b;">{{ $product?->name ?? 'Produto' }}</span>
@if ($variant)
<br><span style="font-size: 13px; color: #71717a;">{{ $variant->label }}</span>
@endif
<br><span style="font-size: 12px; color: #a1a1aa; margin-top: 8px; display: inline-block;">Corra, o estoque pode ser limitado!</span>
</td>
</tr>
</table>

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Comprar Agora
@endcomponent

<p style="text-align: center; color: #a1a1aa; font-size: 11px;">Este e-mail foi enviado porque você se cadastrou para ser avisado sobre a disponibilidade deste produto.</p>
@endcomponent
