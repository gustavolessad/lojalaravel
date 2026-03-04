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

class SeoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Geral::class;
    protected static ?string $navigationIcon  = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationLabel = 'SEO e Analytics';
    protected static ?string $title           = 'SEO e Analytics';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.pages.seo-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'seo_meta_title'        => Setting::get('seo_meta_title', ''),
            'seo_meta_description'  => Setting::get('seo_meta_description', ''),
            'seo_meta_keywords'     => Setting::get('seo_meta_keywords', ''),
            'seo_og_image'          => Setting::get('seo_og_image', ''),
            'seo_google_analytics'  => Setting::get('seo_google_analytics', ''),
            'seo_google_tag_manager'=> Setting::get('seo_google_tag_manager', ''),
            'seo_facebook_pixel'    => Setting::get('seo_facebook_pixel', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Meta Tags Padrão')
                    ->description('Usadas nas páginas que não têm meta tags próprias (ex: homepage, listagens).')
                    ->schema([
                        Forms\Components\TextInput::make('seo_meta_title')
                            ->label('Título Padrão (meta title)')
                            ->maxLength(60)
                            ->live(debounce: '500ms')
                            ->hint(fn (?string $state): string => mb_strlen($state ?? '') . '/60')
                            ->hintColor(fn (?string $state) => mb_strlen($state ?? '') > 60 ? 'danger' : (mb_strlen($state ?? '') > 50 ? 'warning' : null))
                            ->helperText('Aparece na aba do navegador e nos resultados do Google.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('seo_meta_description')
                            ->label('Descrição Padrão (meta description)')
                            ->rows(3)
                            ->maxLength(160)
                            ->live(debounce: '500ms')
                            ->hint(fn (?string $state): string => mb_strlen($state ?? '') . '/160')
                            ->hintColor(fn (?string $state) => mb_strlen($state ?? '') > 160 ? 'danger' : (mb_strlen($state ?? '') > 150 ? 'warning' : null))
                            ->helperText('Aparece como resumo nos resultados de busca.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('seo_meta_keywords')
                            ->label('Palavras-chave Padrão')
                            ->maxLength(255)
                            ->placeholder('palavra1, palavra2, palavra3')
                            ->helperText('Separadas por vírgula. Usadas quando a página não tem palavras-chave próprias.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('seo_og_image')
                            ->label('Imagem de compartilhamento (OG Image)')
                            ->image()
                            ->disk('public')
                            ->directory('seo')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1.91:1')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('630')
                            ->helperText('Imagem padrão ao compartilhar links da loja nas redes sociais. Recomendado: 1200×630px.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Códigos de Rastreamento')
                    ->description('Insira apenas os IDs — os scripts são inseridos automaticamente nas páginas.')
                    ->schema([
                        Forms\Components\TextInput::make('seo_google_analytics')
                            ->label('Google Analytics 4 — ID de Medição')
                            ->placeholder('G-XXXXXXXXXX')
                            ->maxLength(20)
                            ->helperText('Encontrado em GA4 → Administrador → Fluxos de dados → ID de medição.'),

                        Forms\Components\TextInput::make('seo_google_tag_manager')
                            ->label('Google Tag Manager — ID do Contêiner')
                            ->placeholder('GTM-XXXXXXX')
                            ->maxLength(15)
                            ->helperText('Encontrado em GTM → Administrador → ID do contêiner.'),

                        Forms\Components\TextInput::make('seo_facebook_pixel')
                            ->label('Facebook Pixel — ID')
                            ->placeholder('000000000000000')
                            ->maxLength(20)
                            ->helperText('Encontrado no Gerenciador de Eventos do Meta (Facebook).'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'seo_meta_title'         => trim($state['seo_meta_title'] ?? ''),
            'seo_meta_description'   => trim($state['seo_meta_description'] ?? ''),
            'seo_meta_keywords'      => trim($state['seo_meta_keywords'] ?? ''),
            'seo_og_image'           => $state['seo_og_image'] ?? '',
            'seo_google_analytics'   => trim($state['seo_google_analytics'] ?? ''),
            'seo_google_tag_manager' => trim($state['seo_google_tag_manager'] ?? ''),
            'seo_facebook_pixel'     => trim($state['seo_facebook_pixel'] ?? ''),
        ]);

        Notification::make()
            ->title('Configurações de SEO salvas!')
            ->success()
            ->send();
    }
}
