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

class SelosSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Geral::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Selos';
    protected static ?string $title           = 'Selos';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.selos-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'seal_payment'   => Setting::get('seal_payment') ? [Setting::get('seal_payment')] : [],
            'seal_security'  => Setting::get('seal_security') ? [Setting::get('seal_security')] : [],
            'seal_transport' => Setting::get('seal_transport') ? [Setting::get('seal_transport')] : [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Selos do Rodapé')
                    ->description('Imagens de selos exibidas no rodapé da loja para transmitir confiança ao cliente.')
                    ->schema([
                        Forms\Components\FileUpload::make('seal_payment')
                            ->label('Selo de Pagamentos')
                            ->image()
                            ->disk('public')
                            ->directory('store/seals')
                            ->visibility('public')
                            ->imagePreviewHeight('80')
                            ->helperText('Ex: bandeiras de cartão, PIX, boleto. PNG ou SVG transparente recomendado.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('seal_security')
                            ->label('Selo de Segurança')
                            ->image()
                            ->disk('public')
                            ->directory('store/seals')
                            ->visibility('public')
                            ->imagePreviewHeight('80')
                            ->helperText('Ex: SSL, Site Seguro, compra protegida. PNG ou SVG transparente recomendado.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('seal_transport')
                            ->label('Selo de Transportes')
                            ->image()
                            ->disk('public')
                            ->directory('store/seals')
                            ->visibility('public')
                            ->imagePreviewHeight('80')
                            ->helperText('Ex: Correios, Jadlog, transportadoras parceiras. PNG ou SVG transparente recomendado.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'seal_payment'   => is_array($state['seal_payment']) ? ($state['seal_payment'][0] ?? '') : ($state['seal_payment'] ?? ''),
            'seal_security'  => is_array($state['seal_security']) ? ($state['seal_security'][0] ?? '') : ($state['seal_security'] ?? ''),
            'seal_transport' => is_array($state['seal_transport']) ? ($state['seal_transport'][0] ?? '') : ($state['seal_transport'] ?? ''),
        ]);

        Notification::make()
            ->title('Selos salvos!')
            ->success()
            ->send();
    }
}
