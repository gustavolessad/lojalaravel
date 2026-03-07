<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Pagamento;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AsaasSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Pagamento::class;
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Asaas';
    protected static ?string $title           = 'Asaas — Configurações';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.asaas-settings';

    public function getBreadcrumb(): string
    {
        return 'Asaas';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $methods = Setting::get('payment_asaas_methods', 'pix,credit_card');

        $this->form->fill([
            'payment_asaas_sandbox'       => (bool) Setting::get('payment_asaas_sandbox', true),
            'payment_asaas_token'         => Setting::get('payment_asaas_token', ''),
            'payment_asaas_webhook_token' => Setting::get('payment_asaas_webhook_token', ''),
            'payment_asaas_methods'       => array_filter(explode(',', $methods)),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Credenciais')
                    ->description('Configure a integração com o Asaas para processar PIX e cartão de crédito.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_asaas_sandbox')
                            ->label('Modo Sandbox (ambiente de testes)')
                            ->helperText('Desative para usar o ambiente de produção e cobranças reais.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_asaas_token')
                            ->label('Token de API')
                            ->password()
                            ->revealable()
                            ->helperText('Encontrado em Asaas → Minha Conta → Integrações → Chave de API.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_asaas_webhook_token')
                            ->label('Token de Webhook')
                            ->password()
                            ->revealable()
                            ->helperText('Gere um token no Asaas (Configurações → Webhooks) e cole aqui. URL do webhook: ' . url('/webhook/asaas'))
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Métodos de pagamento')
                    ->description('Selecione quais métodos de pagamento ficarão disponíveis no checkout quando o Asaas estiver ativo.')
                    ->schema([
                        Forms\Components\CheckboxList::make('payment_asaas_methods')
                            ->label('')
                            ->options([
                                'pix'         => 'PIX',
                                'credit_card' => 'Cartão de crédito',
                                'boleto'      => 'Boleto bancário',
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'payment_asaas_sandbox'       => $state['payment_asaas_sandbox'] ? '1' : '0',
            'payment_asaas_token'         => trim($state['payment_asaas_token'] ?? ''),
            'payment_asaas_webhook_token' => trim($state['payment_asaas_webhook_token'] ?? ''),
            'payment_asaas_methods'       => implode(',', $state['payment_asaas_methods'] ?? ['pix', 'credit_card']),
        ]);

        Notification::make()
            ->title('Configurações do Asaas salvas!')
            ->success()
            ->send();
    }
}
