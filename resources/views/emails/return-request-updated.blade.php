@component('mail::message')
{{-- Ícone --}}
<div style="text-align: center; margin-bottom: 24px;">
<div style="display: inline-block; width: 56px; height: 56px; background-color: #f0fdf4; border-radius: 50%; line-height: 56px; text-align: center;">
<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMxNTgwM2QiIHN0cm9rZS13aWR0aD0iMS43NSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTYuMDIzIDkuMzQ4aDQuOTkydi0uMDAxTTIuOTg1IDE5LjY0NHYtNC45OTJtMCAwaDQuOTkybS00Ljk5MyAwIDMuMTgxIDMuMTgzYTguMjUgOC4yNSAwIDAgMCAxMy44MDMtMy43TTQuMDMxIDkuODY1YTguMjUgOC4yNSAwIDAgMSAxMy44MDMtMy43bDMuMTgxIDMuMTgybTAtNC45OTF2NC45OSIvPjwvc3ZnPg==" alt="" style="vertical-align: middle; width: 28px; height: 28px;">
</div>
</div>

<div style="text-align: center; margin-bottom: 8px;">
<span style="font-size: 20px; font-weight: 700; color: #18181b;">Solicitação atualizada</span>
</div>

<p style="text-align: center; color: #71717a; margin-bottom: 28px;">Olá, <strong style="color: #18181b;">{{ $returnRequest->customer?->display_name }}</strong>! Sua solicitação referente ao Pedido #{{ $returnRequest->order?->order_number }} foi atualizada.</p>

{{-- Detalhes da solicitação --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 12px; padding: 20px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="text-align: center; padding-bottom: 12px; border-bottom: 1px solid #e4e4e7;">
<span style="font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px;">Solicitação</span><br>
<span style="font-size: 18px; font-weight: 700; color: #18181b;">#{{ $returnRequest->id }}</span>
</td>
</tr>
<tr>
<td style="padding-top: 12px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Tipo</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">{{ $returnRequest->type_label }}</td>
</tr>
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Motivo</td>
<td style="font-size: 13px; color: #3f3f46; text-align: right; padding: 4px 0;">{{ $returnRequest->reason_label }}</td>
</tr>
<tr>
<td style="font-size: 13px; color: #71717a; padding: 4px 0;">Status</td>
<td style="font-size: 13px; font-weight: 600; color: #18181b; text-align: right; padding: 4px 0;">{{ $returnRequest->status_label }}</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

@if ($returnRequest->admin_notes)
{{-- Resposta da loja --}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 24px;">
<tr>
<td style="background-color: #eff6ff; border: 1px solid #dbeafe; border-radius: 12px; padding: 16px;">
<p style="font-size: 13px; font-weight: 600; color: #1e40af; margin: 0 0 8px;">Resposta da loja</p>
<p style="font-size: 13px; color: #3b82f6; margin: 0; line-height: 1.6;">{{ $returnRequest->admin_notes }}</p>
</td>
</tr>
</table>
@endif

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver Minha Solicitação
@endcomponent

<p style="text-align: center; color: #a1a1aa; font-size: 12px;">Em caso de dúvidas, entre em contato conosco.</p>
@endcomponent
