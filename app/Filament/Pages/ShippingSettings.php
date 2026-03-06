<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Logistica;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
            // Geral
            'shipping_origin_cep'      => Setting::get('shipping_origin_cep'),
            'shipping_additional_days' => (int) Setting::get('shipping_additional_days', 0),

            // Melhor Envio
            'shipping_melhorenvio_active' => (bool) Setting::get('shipping_melhorenvio_active', false),
            'shipping_token'             => Setting::get('shipping_token'),
            'shipping_sandbox'           => (bool) Setting::get('shipping_sandbox', true),
            'shipping_services'          => $selectedServices,

            // Braspress
            'shipping_braspress_active'          => (bool) Setting::get('shipping_braspress_active', false),
            'shipping_braspress_cnpj'            => Setting::get('shipping_braspress_cnpj'),
            'shipping_braspress_usuario'         => Setting::get('shipping_braspress_usuario'),
            'shipping_braspress_senha'           => Setting::get('shipping_braspress_senha'),
            'shipping_braspress_additional_days' => (int) Setting::get('shipping_braspress_additional_days', 0),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configurações Gerais')
                    ->description('Configurações compartilhadas entre todas as integrações de frete.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_origin_cep')
                            ->label('CEP de Origem')
                            ->mask('99999-999')
                            ->helperText('CEP do local de onde os pedidos são enviados. Utilizado por todas as integrações.')
                            ->maxLength(9),

                        Forms\Components\TextInput::make('shipping_additional_days')
                            ->label('Dias Adicionais de Prazo')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->helperText('Dias de processamento interno somados ao prazo de todas as transportadoras.')
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Melhor Envio')
                    ->description('Integração com o Melhor Envio para cotações de frete em tempo real.')
                    ->icon('heroicon-o-truck')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Toggle::make('shipping_melhorenvio_active')
                            ->label('Integração ativa')
                            ->helperText('Ative para utilizar o Melhor Envio nas cotações de frete.')
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('shipping_token')
                            ->label('Token de Acesso')
                            ->password()
                            ->revealable()
                            ->helperText('Gere em melhorenvio.com.br → Minha Conta → Tokens de Acesso.')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_melhorenvio_active')),

                        Forms\Components\Toggle::make('shipping_sandbox')
                            ->label('Modo Sandbox (ambiente de testes)')
                            ->helperText('Desative para usar cotações reais em produção.')
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_melhorenvio_active')),

                        Forms\Components\CheckboxList::make('shipping_services')
                            ->label('Serviços exibidos no checkout')
                            ->helperText('Deixe em branco para exibir todos os serviços disponíveis para o CEP de destino.')
                            ->options(collect(self::ME_SERVICES)->mapWithKeys(fn ($name, $id) => [(string) $id => $name]))
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_melhorenvio_active')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Braspress')
                    ->description('Integração com a Braspress para cotações de frete rodoviário.')
                    ->icon('heroicon-o-truck')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Toggle::make('shipping_braspress_active')
                            ->label('Integração ativa')
                            ->helperText('Ative para utilizar a Braspress nas cotações de frete.')
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('shipping_braspress_cnpj')
                            ->label('CNPJ Remetente')
                            ->mask('99.999.999/9999-99')
                            ->maxLength(18)
                            ->helperText('CNPJ da empresa remetente cadastrado na Braspress.')
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_braspress_active')),

                        Forms\Components\TextInput::make('shipping_braspress_usuario')
                            ->label('Usuário')
                            ->helperText('E-mail ou login de acesso à API da Braspress.')
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_braspress_active')),

                        Forms\Components\TextInput::make('shipping_braspress_senha')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->helperText('Senha de acesso à API da Braspress.')
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_braspress_active')),

                        Forms\Components\TextInput::make('shipping_braspress_additional_days')
                            ->label('Dias Adicionais de Prazo')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->helperText('Dias extras somados ao prazo informado pela Braspress (além dos dias gerais).')
                            ->default(0)
                            ->visible(fn (Get $get): bool => (bool) $get('shipping_braspress_active')),
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
            // Melhor Envio
            'shipping_melhorenvio_active' => $state['shipping_melhorenvio_active'] ? '1' : '0',
            'shipping_token'             => trim($state['shipping_token'] ?? ''),
            'shipping_sandbox'           => $state['shipping_sandbox'] ? '1' : '0',
            'shipping_origin_cep'        => preg_replace('/\D/', '', (string) ($state['shipping_origin_cep'] ?? '')),
            'shipping_additional_days'   => (int) ($state['shipping_additional_days'] ?? 0),
            'shipping_services'          => $services,

            // Braspress
            'shipping_braspress_active'          => $state['shipping_braspress_active'] ? '1' : '0',
            'shipping_braspress_cnpj'            => preg_replace('/\D/', '', (string) ($state['shipping_braspress_cnpj'] ?? '')),
            'shipping_braspress_usuario'         => trim($state['shipping_braspress_usuario'] ?? ''),
            'shipping_braspress_senha'           => trim($state['shipping_braspress_senha'] ?? ''),
            'shipping_braspress_additional_days' => (int) ($state['shipping_braspress_additional_days'] ?? 0),
        ]);

        Notification::make()
            ->title('Configurações de frete salvas!')
            ->success()
            ->send();
    }
}
