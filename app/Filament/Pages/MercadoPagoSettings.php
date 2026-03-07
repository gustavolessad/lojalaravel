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

class MercadoPagoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Pagamento::class;
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Mercado Pago';
    protected static ?string $title           = 'Mercado Pago — Configurações';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.pages.mercadopago-settings';

    public function getBreadcrumb(): string
    {
        return 'Mercado Pago';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $methods = Setting::get('payment_mercadopago_methods', 'pix,credit_card');

        $this->form->fill([
            'payment_mercadopago_token'      => Setting::get('payment_mercadopago_token', ''),
            'payment_mercadopago_public_key' => Setting::get('payment_mercadopago_public_key', ''),
            'payment_mercadopago_methods'    => array_filter(explode(',', $methods)),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Credenciais')
                    ->description('Configure a integração com o Mercado Pago para processar PIX, cartão de crédito e boleto.')
                    ->schema([
                        Forms\Components\TextInput::make('payment_mercadopago_token')
                            ->label('Access Token')
                            ->password()
                            ->revealable()
                            ->helperText('Token de acesso gerado em "Suas integrações" no Mercado Pago Developers. Use o token de teste para sandbox.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_mercadopago_public_key')
                            ->label('Public Key')
                            ->password()
                            ->revealable()
                            ->helperText('Chave pública para tokenização de cartão no navegador via MercadoPago.js.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Métodos de pagamento')
                    ->description('Selecione quais métodos de pagamento ficarão disponíveis no checkout quando o Mercado Pago estiver ativo.')
                    ->schema([
                        Forms\Components\CheckboxList::make('payment_mercadopago_methods')
                            ->label('')
                            ->options([
                                'pix'         => 'PIX',
                                'credit_card' => 'Cartão de crédito',
                                'boleto'      => 'Boleto bancário',
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Informações')
                    ->schema([
                        Forms\Components\Placeholder::make('webhook_url')
                            ->label('URL do Webhook')
                            ->content(url('/webhook/mercadopago'))
                            ->helperText('Configure esta URL nas notificações da sua integração no Mercado Pago Developers.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'payment_mercadopago_token'      => trim($state['payment_mercadopago_token'] ?? ''),
            'payment_mercadopago_public_key' => trim($state['payment_mercadopago_public_key'] ?? ''),
            'payment_mercadopago_methods'    => implode(',', $state['payment_mercadopago_methods'] ?? ['pix', 'credit_card']),
        ]);

        Notification::make()
            ->title('Configurações do Mercado Pago salvas!')
            ->success()
            ->send();
    }
}
