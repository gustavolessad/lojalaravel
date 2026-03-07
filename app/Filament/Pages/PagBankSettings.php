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
use Illuminate\Support\Facades\Http;

class PagBankSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Pagamento::class;
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'PagBank';
    protected static ?string $title           = 'PagBank — Configurações';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.pages.pagbank-settings';

    public function getBreadcrumb(): string
    {
        return 'PagBank';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $methods = Setting::get('payment_pagbank_methods', 'pix,credit_card');

        $this->form->fill([
            'payment_pagbank_sandbox'    => (bool) Setting::get('payment_pagbank_sandbox', true),
            'payment_pagbank_token'      => Setting::get('payment_pagbank_token', ''),
            'payment_pagbank_public_key' => Setting::get('payment_pagbank_public_key', ''),
            'payment_pagbank_methods'    => array_filter(explode(',', $methods)),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Credenciais')
                    ->description('Configure a integração com o PagBank (PagSeguro) para processar PIX e cartão de crédito.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_pagbank_sandbox')
                            ->label('Modo Sandbox (ambiente de testes)')
                            ->helperText('Desative para usar o ambiente de produção e cobranças reais.')
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_pagbank_token')
                            ->label('Token de API')
                            ->password()
                            ->revealable()
                            ->helperText('Token gerado no Portal do Desenvolvedor PagBank.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_pagbank_public_key')
                            ->label('Chave Pública (Public Key)')
                            ->password()
                            ->revealable()
                            ->helperText('Necessária para criptografar o cartão de crédito no navegador. Clique em "Gerar chave pública" para criar automaticamente.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Métodos de pagamento')
                    ->description('Selecione quais métodos de pagamento ficarão disponíveis no checkout quando o PagBank estiver ativo.')
                    ->schema([
                        Forms\Components\CheckboxList::make('payment_pagbank_methods')
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
                            ->content(url('/webhook/pagbank'))
                            ->helperText('Configure esta URL nas notificações do PagBank para receber confirmações de pagamento.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'payment_pagbank_sandbox'    => $state['payment_pagbank_sandbox'] ? '1' : '0',
            'payment_pagbank_token'      => trim($state['payment_pagbank_token'] ?? ''),
            'payment_pagbank_public_key' => trim($state['payment_pagbank_public_key'] ?? ''),
            'payment_pagbank_methods'    => implode(',', $state['payment_pagbank_methods'] ?? ['pix', 'credit_card']),
        ]);

        Notification::make()
            ->title('Configurações do PagBank salvas!')
            ->success()
            ->send();
    }

    /**
     * Gera a chave pública via API do PagBank e salva automaticamente.
     */
    public function generatePublicKey(): void
    {
        $state   = $this->form->getState();
        $token   = trim($state['payment_pagbank_token'] ?? '');
        $sandbox = (bool) ($state['payment_pagbank_sandbox'] ?? true);

        if (! $token) {
            Notification::make()
                ->title('Preencha o Token de API antes de gerar a chave pública.')
                ->danger()
                ->send();
            return;
        }

        $baseUrl = $sandbox
            ? 'https://sandbox.api.pagseguro.com'
            : 'https://api.pagseguro.com';

        try {
            $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->timeout(15)
                ->post("{$baseUrl}/public-keys", [
                    'type' => 'card',
                ]);

            if (! $response->successful()) {
                $body = $response->json();
                $msg  = $body['error_messages'][0]['description']
                    ?? $body['message']
                    ?? "Erro HTTP {$response->status()}";

                Notification::make()
                    ->title('Falha ao gerar chave pública')
                    ->body($msg)
                    ->danger()
                    ->send();
                return;
            }

            $publicKey = $response->json('public_key') ?? '';

            if (! $publicKey) {
                Notification::make()
                    ->title('Resposta inesperada da API — chave pública não encontrada.')
                    ->danger()
                    ->send();
                return;
            }

            // Salva no banco e atualiza o formulário
            Setting::set('payment_pagbank_public_key', $publicKey);

            $this->form->fill(array_merge($state, [
                'payment_pagbank_public_key' => $publicKey,
            ]));

            Notification::make()
                ->title('Chave pública gerada com sucesso!')
                ->body('A chave foi salva automaticamente.')
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erro de conexão com o PagBank')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
