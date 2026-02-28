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

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Pagamento::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?string $title           = 'Configurações de Pagamento';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.payment-settings';

    public function getBreadcrumb(): string
    {
        return 'Configurações';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'payment_card_display'           => Setting::get('payment_card_display', 'both'),
            'payment_pix_discount'           => (float) Setting::get('payment_pix_discount', 0),
            'payment_installments_max'       => (int) Setting::get('payment_installments_max', 12),
            'payment_installments_free'      => (int) Setting::get('payment_installments_free', 1),
            'payment_installment_min_value'  => (float) Setting::get('payment_installment_min_value', 10.00),
            'payment_interest_rate'          => (float) Setting::get('payment_interest_rate', 0),
            'payment_asaas_sandbox'          => (bool) Setting::get('payment_asaas_sandbox', true),
            'payment_asaas_token'            => Setting::get('payment_asaas_token', ''),
            'payment_asaas_webhook_token'    => Setting::get('payment_asaas_webhook_token', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Exibição nos produtos')
                    ->description('Defina o que será exibido abaixo do preço nos cards e na página do produto.')
                    ->schema([
                        Forms\Components\Select::make('payment_card_display')
                            ->label('Exibir nos cards e página do produto')
                            ->options([
                                'pix'          => 'Somente preço PIX',
                                'installments' => 'Somente parcelamento',
                                'both'         => 'PIX e parcelamento',
                                'none'         => 'Não exibir',
                            ])
                            ->default('both')
                            ->selectablePlaceholder(false)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Desconto e parcelamento')
                    ->description('Configure o desconto para pagamento via PIX e as condições de parcelamento no cartão de crédito.')
                    ->schema([
                        Forms\Components\TextInput::make('payment_pix_discount')
                            ->label('Desconto PIX')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0)
                            ->helperText('0 = sem desconto. O preço PIX será: total × (1 − desconto / 100).'),

                        Forms\Components\TextInput::make('payment_installments_max')
                            ->label('Parcelas máximas')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(12)
                            ->helperText('Número máximo de parcelas oferecidas no checkout.'),

                        Forms\Components\TextInput::make('payment_installments_free')
                            ->label('Parcelas sem juros')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(1)
                            ->helperText('Parcelas até este número são sem juros; acima disso, aplica-se a taxa mensal.'),

                        Forms\Components\TextInput::make('payment_installment_min_value')
                            ->label('Valor mínimo por parcela')
                            ->numeric()
                            ->minValue(1)
                            ->prefix('R$')
                            ->default(10.00)
                            ->helperText('Parcelamento para quando o valor da parcela cair abaixo deste valor.'),

                        Forms\Components\TextInput::make('payment_interest_rate')
                            ->label('Taxa de juros mensal')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('% a.m.')
                            ->default(0)
                            ->helperText('Aplica-se nas parcelas acima do limite sem juros (Tabela Price). 0 = sem juros em todas as parcelas.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Asaas — Gateway de Pagamento')
                    ->description('Configure a integração com o gateway Asaas para processar PIX e cartão de crédito.')
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
                            ->helperText('Gere um token no Asaas (Configurações → Webhooks) e cole aqui. A URL do webhook é: ' . url('/webhook/asaas'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'payment_card_display'          => $state['payment_card_display'] ?? 'both',
            'payment_pix_discount'          => (float) ($state['payment_pix_discount'] ?? 0),
            'payment_installments_max'      => (int) ($state['payment_installments_max'] ?? 12),
            'payment_installments_free'     => (int) ($state['payment_installments_free'] ?? 1),
            'payment_installment_min_value' => (float) ($state['payment_installment_min_value'] ?? 10.00),
            'payment_interest_rate'         => (float) ($state['payment_interest_rate'] ?? 0),
            'payment_asaas_sandbox'         => $state['payment_asaas_sandbox'] ? '1' : '0',
            'payment_asaas_token'           => trim($state['payment_asaas_token'] ?? ''),
            'payment_asaas_webhook_token'   => trim($state['payment_asaas_webhook_token'] ?? ''),
        ]);

        Notification::make()
            ->title('Configurações de pagamento salvas!')
            ->success()
            ->send();
    }
}
