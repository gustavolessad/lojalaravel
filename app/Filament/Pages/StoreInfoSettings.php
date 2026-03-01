<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Geral;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StoreInfoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Geral::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Informações da Loja';
    protected static ?string $title           = 'Informações da Loja';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.store-info-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'store_name'         => Setting::get('store_name', ''),
            'store_slogan'       => Setting::get('store_slogan', ''),
            'store_logo'         => Setting::get('store_logo') ? [Setting::get('store_logo')] : [],
            'store_logo_footer'  => Setting::get('store_logo_footer') ? [Setting::get('store_logo_footer')] : [],
            'store_address'      => Setting::get('store_address', ''),
            'store_hours'        => Setting::get('store_hours', ''),
            'store_cpf_cnpj'     => Setting::get('store_cpf_cnpj', ''),
            'store_razao_social' => Setting::get('store_razao_social', ''),
            'store_phone'        => Setting::get('store_phone', ''),
            'store_whatsapp'     => Setting::get('store_whatsapp', ''),
            'store_email'        => Setting::get('store_email', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identidade')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->label('Nome da Loja')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('store_slogan')
                            ->label('Slogan')
                            ->maxLength(200)
                            ->helperText('Frase curta exibida abaixo do nome da loja.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('store_logo')
                            ->label('Logo principal')
                            ->image()
                            ->disk('public')
                            ->directory('store')
                            ->visibility('public')
                            ->imagePreviewHeight('64')
                            ->helperText('Exibido no cabeçalho da loja. Formatos: PNG, SVG, WEBP.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('store_logo_footer')
                            ->label('Logo do rodapé')
                            ->image()
                            ->disk('public')
                            ->directory('store')
                            ->visibility('public')
                            ->imagePreviewHeight('64')
                            ->helperText('Versão alternativa para o rodapé (ex: versão clara sobre fundo escuro).')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Dados Jurídicos')
                    ->schema([
                        Forms\Components\TextInput::make('store_cpf_cnpj')
                            ->label('CPF / CNPJ')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('store_razao_social')
                            ->label('Razão Social')
                            ->maxLength(200),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Localização e Funcionamento')
                    ->schema([
                        Forms\Components\Textarea::make('store_address')
                            ->label('Endereço')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('store_hours')
                            ->label('Horário de Atendimento')
                            ->maxLength(200)
                            ->placeholder('Seg a Sex, 9h às 18h')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Contato')
                    ->schema([
                        Forms\Components\TextInput::make('store_phone')
                            ->label('Telefone de Atendimento')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('store_whatsapp')
                            ->label('WhatsApp de Atendimento')
                            ->tel()
                            ->maxLength(20)
                            ->helperText('Somente números com DDD. Ex: 11999999999'),

                        Forms\Components\TextInput::make('store_email')
                            ->label('E-mail de Atendimento')
                            ->email()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'store_name'         => trim($state['store_name'] ?? ''),
            'store_slogan'       => trim($state['store_slogan'] ?? ''),
            'store_logo'         => is_array($state['store_logo']) ? ($state['store_logo'][0] ?? '') : ($state['store_logo'] ?? ''),
            'store_logo_footer'  => is_array($state['store_logo_footer']) ? ($state['store_logo_footer'][0] ?? '') : ($state['store_logo_footer'] ?? ''),
            'store_address'      => trim($state['store_address'] ?? ''),
            'store_hours'        => trim($state['store_hours'] ?? ''),
            'store_cpf_cnpj'     => trim($state['store_cpf_cnpj'] ?? ''),
            'store_razao_social' => trim($state['store_razao_social'] ?? ''),
            'store_phone'        => trim($state['store_phone'] ?? ''),
            'store_whatsapp'     => trim($state['store_whatsapp'] ?? ''),
            'store_email'        => trim($state['store_email'] ?? ''),
        ]);

        Notification::make()
            ->title('Informações da loja salvas!')
            ->success()
            ->send();
    }
}
