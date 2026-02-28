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

class ExternalScriptsSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Geral::class;
    protected static ?string $navigationIcon  = 'heroicon-o-code-bracket';
    protected static ?string $navigationLabel = 'Scripts Externos';
    protected static ?string $title           = 'Scripts Externos';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.external-scripts-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'script_head'     => Setting::get('script_head', ''),
            'script_body_end' => Setting::get('script_body_end', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Scripts no <head>')
                    ->description('Inseridos antes do fechamento da tag </head>. Use para Google Tag Manager (snippet do head), fontes externas, estilos personalizados, etc.')
                    ->schema([
                        Forms\Components\Textarea::make('script_head')
                            ->label('')
                            ->rows(8)
                            ->placeholder("<!-- Exemplo: Google Tag Manager -->\n<script>(function(w,d,s,l,i){...})(window,document,'script','dataLayer','GTM-XXXXXX');</script>")
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ]),

                Forms\Components\Section::make('Scripts antes do </body>')
                    ->description('Inseridos antes do fechamento da tag </body>. Use para Google Tag Manager (snippet do body), chat online, pixels de conversão, etc.')
                    ->schema([
                        Forms\Components\Textarea::make('script_body_end')
                            ->label('')
                            ->rows(8)
                            ->placeholder("<!-- Exemplo: GTM noscript -->\n<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=GTM-XXXXXX\"...></iframe></noscript>")
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'script_head'     => $state['script_head'] ?? '',
            'script_body_end' => $state['script_body_end'] ?? '',
        ]);

        Notification::make()
            ->title('Scripts externos salvos!')
            ->success()
            ->send();
    }
}
