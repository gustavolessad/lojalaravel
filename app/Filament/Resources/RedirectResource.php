<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Geral;
use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Content\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $cluster                = Geral::class;
    protected static ?string $navigationIcon        = 'heroicon-o-arrow-uturn-right';
    protected static ?string $navigationLabel       = 'Redirecionamentos';
    protected static ?string $modelLabel            = 'Redirecionamento';
    protected static ?string $pluralModelLabel      = 'Redirecionamentos';
    protected static ?int    $navigationSort        = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('from_url')
                    ->label('URL de origem')
                    ->prefix(url('/'))
                    ->placeholder('/url-antiga')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Caminho relativo, ex: /produto-antigo/p'),

                Forms\Components\TextInput::make('to_url')
                    ->label('URL de destino')
                    ->placeholder('/url-nova ou https://...')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Caminho relativo ou URL completa'),

                Forms\Components\Select::make('status_code')
                    ->label('Tipo')
                    ->options([
                        301 => '301 — Permanente (recomendado para SEO)',
                        302 => '302 — Temporário',
                    ])
                    ->default(301)
                    ->required(),

                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_url')
                    ->label('De')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('to_url')
                    ->label('Para')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (int $state) => $state === 301 ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('hits')
                    ->label('Acessos')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('last_hit_at')
                    ->label('Último acesso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit'   => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }

    public static function afterSave(): void
    {
        Cache::forget('seo_redirects');
    }
}
