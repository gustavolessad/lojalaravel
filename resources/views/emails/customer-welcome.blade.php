@component('mail::message')
# Bem-vindo(a) à {{ config('app.name') }}!

Olá, **{{ $customer->display_name }}**!

Estamos muito felizes em tê-lo(a) conosco. Sua conta foi criada com sucesso e você já pode aproveitar todos os benefícios:

@component('mail::panel')
✔ Acompanhe seus pedidos em tempo real
✔ Salve endereços para agilizar suas compras
✔ Receba notificações quando produtos favoritos voltarem ao estoque
✔ Acesse seu histórico de compras a qualquer momento
@endcomponent

@component('mail::button', ['url' => $accountUrl, 'color' => 'primary'])
Acessar Minha Conta
@endcomponent

Ou continue explorando nossa loja:

@component('mail::button', ['url' => $storeUrl, 'color' => 'success'])
Ver Produtos
@endcomponent

Qualquer dúvida, estamos à disposição!

Até logo,
{{ config('app.name') }}
@endcomponent
