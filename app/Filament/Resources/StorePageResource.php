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
use Illuminate\Support\HtmlString;
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
                            ->maxLength(60)
                            ->live(debounce: '500ms')
                            ->hint(fn (?string $state): string => mb_strlen($state ?? '') . '/60')
                            ->hintColor(fn (?string $state) => mb_strlen($state ?? '') > 60 ? 'danger' : (mb_strlen($state ?? '') > 50 ? 'warning' : null))
                            ->helperText('Deixe em branco para usar o título da página.'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Descrição SEO (meta description)')
                            ->rows(2)
                            ->maxLength(160)
                            ->live(debounce: '500ms')
                            ->hint(fn (?string $state): string => mb_strlen($state ?? '') . '/160')
                            ->hintColor(fn (?string $state) => mb_strlen($state ?? '') > 160 ? 'danger' : (mb_strlen($state ?? '') > 150 ? 'warning' : null)),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Palavras-chave SEO')
                            ->maxLength(255)
                            ->placeholder('palavra1, palavra2, palavra3')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('serp_preview')
                            ->label('')
                            ->content(function (Forms\Get $get): HtmlString {
                                $title = $get('meta_title') ?: $get('title') ?: 'Título da página';
                                $desc = $get('meta_description') ?: 'Adicione uma descrição SEO para visualizar...';
                                $slug = $get('slug') ?: 'pagina';
                                $url = url("/pagina/{$slug}");

                                return new HtmlString(
                                    '<div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">'
                                    . '<p class="text-xs font-medium text-gray-400 mb-2">Preview do Google</p>'
                                    . '<cite class="text-sm text-[#202124] dark:text-gray-300 not-italic">' . e($url) . '</cite>'
                                    . '<h3 class="text-xl text-[#1a0dab] dark:text-blue-400 leading-snug truncate mt-0.5">' . e(mb_substr($title, 0, 60)) . '</h3>'
                                    . '<p class="text-sm text-[#4d5156] dark:text-gray-400 mt-0.5 line-clamp-2">' . e(mb_substr($desc, 0, 160)) . '</p>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
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
