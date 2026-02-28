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

class SocialMediaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster         = Geral::class;
    protected static ?string $navigationIcon  = 'heroicon-o-share';
    protected static ?string $navigationLabel = 'Redes Sociais';
    protected static ?string $title           = 'Redes Sociais';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.pages.social-media-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_facebook'  => Setting::get('social_facebook', ''),
            'social_youtube'   => Setting::get('social_youtube', ''),
            'social_tiktok'    => Setting::get('social_tiktok', ''),
            'social_pinterest' => Setting::get('social_pinterest', ''),
            'social_linkedin'  => Setting::get('social_linkedin', ''),
            'social_twitter'   => Setting::get('social_twitter', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Perfis nas Redes Sociais')
                    ->description('Informe as URLs completas dos perfis. Os links serão exibidos no rodapé e em outros locais da loja.')
                    ->schema([
                        Forms\Components\TextInput::make('social_instagram')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/sualojaaqui')
                            ->prefixIcon('heroicon-o-camera'),

                        Forms\Components\TextInput::make('social_facebook')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/sualojaaqui')
                            ->prefixIcon('heroicon-o-globe-alt'),

                        Forms\Components\TextInput::make('social_youtube')
                            ->label('YouTube')
                            ->url()
                            ->placeholder('https://youtube.com/@sualojaaqui')
                            ->prefixIcon('heroicon-o-play-circle'),

                        Forms\Components\TextInput::make('social_tiktok')
                            ->label('TikTok')
                            ->url()
                            ->placeholder('https://tiktok.com/@sualojaaqui')
                            ->prefixIcon('heroicon-o-musical-note'),

                        Forms\Components\TextInput::make('social_pinterest')
                            ->label('Pinterest')
                            ->url()
                            ->placeholder('https://pinterest.com/sualojaaqui')
                            ->prefixIcon('heroicon-o-bookmark'),

                        Forms\Components\TextInput::make('social_linkedin')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('https://linkedin.com/company/sualojaaqui')
                            ->prefixIcon('heroicon-o-briefcase'),

                        Forms\Components\TextInput::make('social_twitter')
                            ->label('X (Twitter)')
                            ->url()
                            ->placeholder('https://x.com/sualojaaqui')
                            ->prefixIcon('heroicon-o-chat-bubble-left'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'social_instagram' => trim($state['social_instagram'] ?? ''),
            'social_facebook'  => trim($state['social_facebook'] ?? ''),
            'social_youtube'   => trim($state['social_youtube'] ?? ''),
            'social_tiktok'    => trim($state['social_tiktok'] ?? ''),
            'social_pinterest' => trim($state['social_pinterest'] ?? ''),
            'social_linkedin'  => trim($state['social_linkedin'] ?? ''),
            'social_twitter'   => trim($state['social_twitter'] ?? ''),
        ]);

        Notification::make()
            ->title('Redes sociais salvas!')
            ->success()
            ->send();
    }
}
