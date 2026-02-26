<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Logistica;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ShippingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster          = Logistica::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationLabel = 'Frete via API';
    protected static ?string $title           = 'Frete via API';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.shipping-settings';

    public function getBreadcrumb(): string
    {
        return 'Frete via API';
    }

    // Serviços disponíveis no Melhor Envio (id => nome)
    public const ME_SERVICES = [
        1  => 'Correios — PAC',
        2  => 'Correios — SEDEX',
        3  => 'Jadlog — Package',
        4  => 'Jadlog — .com',
        7  => 'Loggi',
        9  => 'Correios — Mini Envios',
        17 => 'Azul Cargo Express',
        18 => 'Azul Cargo — Amanhã',
        23 => 'JeT',
        24 => 'Buslog',
    ];

    public ?array $data = [];

    public function mount(): void
    {
        $saved = Setting::get('shipping_services', '');
        $selectedServices = $saved
            ? array_map('strval', array_filter(array_map('intval', explode(',', $saved))))
            : [];

        $this->form->fill([
            'shipping_token'           => Setting::get('shipping_token'),
            'shipping_sandbox'         => (bool) Setting::get('shipping_sandbox', true),
            'shipping_origin_cep'      => Setting::get('shipping_origin_cep'),
            'shipping_additional_days' => (int) Setting::get('shipping_additional_days', 0),
            'shipping_services'        => $selectedServices,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Melhor Envio')
                    ->description('Configure a integração com o Melhor Envio para cotações de frete em tempo real. O token de acesso pode ser gerado em melhorenvio.com.br → Minha Conta → Tokens de Acesso.')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_token')
                            ->label('Token de Acesso')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('shipping_sandbox')
                            ->label('Modo Sandbox (ambiente de testes)')
                            ->helperText('Desative para usar cotações reais em produção.'),

                        Forms\Components\TextInput::make('shipping_origin_cep')
                            ->label('CEP de Origem')
                            ->mask('99999-999')
                            ->helperText('CEP do local de onde os pedidos são enviados.')
                            ->maxLength(9),

                        Forms\Components\TextInput::make('shipping_additional_days')
                            ->label('Dias Adicionais de Prazo')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->helperText('Dias de processamento interno somados ao prazo informado pela transportadora.')
                            ->default(0),

                        Forms\Components\CheckboxList::make('shipping_services')
                            ->label('Serviços exibidos no checkout')
                            ->helperText('Deixe em branco para exibir todos os serviços disponíveis para o CEP de destino.')
                            ->options(collect(self::ME_SERVICES)->mapWithKeys(fn ($name, $id) => [(string) $id => $name]))
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state    = $this->form->getState();
        $services = implode(',', array_filter(array_map('intval', $state['shipping_services'] ?? [])));

        Setting::setMany([
            'shipping_token'           => trim($state['shipping_token'] ?? ''),
            'shipping_sandbox'         => $state['shipping_sandbox'] ? '1' : '0',
            'shipping_origin_cep'      => preg_replace('/\D/', '', (string) ($state['shipping_origin_cep'] ?? '')),
            'shipping_additional_days' => (int) ($state['shipping_additional_days'] ?? 0),
            'shipping_services'        => $services,
        ]);

        Notification::make()
            ->title('Configurações de frete salvas!')
            ->success()
            ->send();
    }
}
