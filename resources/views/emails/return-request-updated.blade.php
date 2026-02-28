@component('mail::message')
# Atualização na sua solicitação

Olá, **{{ $returnRequest->customer?->display_name }}**!

Sua **Solicitação #{{ $returnRequest->id }}** referente ao **Pedido #{{ $returnRequest->order?->order_number }}** foi atualizada.

@component('mail::panel')
**Tipo:** {{ $returnRequest->type_label }}
**Motivo:** {{ $returnRequest->reason_label }}
**Status atual:** {{ $returnRequest->status_label }}
@endcomponent

@if ($returnRequest->admin_notes)
### Resposta da loja

{{ $returnRequest->admin_notes }}

@endif

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Ver minha solicitação
@endcomponent

Em caso de dúvidas, entre em contato conosco.

Atenciosamente,
{{ config('app.name') }}
@endcomponent
