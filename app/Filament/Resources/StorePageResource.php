<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Geral;
use App\Filament\Resources\StorePageResource\Pages;
use App\Models\StorePage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class StorePageResource extends Resource
{
    protected static ?string $model = StorePage::class;

    protected static ?string $cluster          = Geral::class;
    protected static ?string $navigationIcon   = 'heroicon-o-document-text';
    protected static ?string $navigationLabel  = 'Textos e Políticas';
    protected static ?string $modelLabel       = 'Página';
    protected static ?string $pluralModelLabel = 'Textos e Políticas';
    protected static ?int    $navigationSort   = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título da página')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(100)
                            ->unique(StorePage::class, 'slug', ignoreRecord: true)
                            ->helperText('Gerado automaticamente a partir do título. Não altere após publicar.')
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Conteúdo')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Conteúdo')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'h2', 'h3',
                                'bulletList', 'orderedList',
                                'link',
                                'blockquote',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('SEO')
                    ->description('Configurações opcionais para melhorar o posicionamento desta página nos buscadores.')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Título SEO (meta title)')
                            ->maxLength(70)
                            ->helperText('Deixe em branco para usar o título da página.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Descrição SEO (meta description)')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Recomendado: até 155 caracteres.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Página')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn (string $state) => '/pagina/' . $state)
                    ->color('gray')
                    ->url(fn (StorePage $record) => route('page.show', $record->slug))
                    ->openUrlInNewTab(),

                Tables\Columns\BadgeColumn::make('is_system')
                    ->label('Tipo')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Sistema' : 'Customizada')
                    ->color(fn (bool $state) => $state ? 'gray' : 'info'),

                Tables\Columns\IconColumn::make('content')
                    ->label('Conteúdo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn (StorePage $record) => ! empty($record->content))
                    ->tooltip(fn (StorePage $record) => empty($record->content) ? 'Sem conteúdo' : 'Com conteúdo'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última edição')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Excluir')
                    ->visible(fn (StorePage $record) => ! $record->is_system),
            ])
            ->paginated(false)
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStorePages::route('/'),
            'create' => Pages\CreateStorePage::route('/create'),
            'edit'   => Pages\EditStorePage::route('/{record}/edit'),
        ];
    }
}
