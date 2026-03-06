<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Marketing\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon   = 'heroicon-o-ticket';
    protected static ?string $navigationLabel  = 'Cupons';
    protected static ?string $navigationGroup  = 'Marketing';
    protected static ?string $modelLabel       = 'Cupom';
    protected static ?string $pluralModelLabel = 'Cupons';
    protected static ?int    $navigationSort   = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Código do cupom')
                        ->placeholder('Ex: NATAL20')
                        ->required()
                        ->maxLength(50)
                        ->helperText('Sempre salvo em maiúsculas. Compartilhe este código com os clientes.'),

                    Forms\Components\TextInput::make('description')
                        ->label('Descrição interna')
                        ->placeholder('Ex: Campanha de Natal 2026')
                        ->maxLength(200)
                        ->helperText('Apenas para controle interno, não aparece para o cliente.'),
                ]),

            Forms\Components\Section::make('Desconto')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipo de desconto')
                        ->options([
                            'percent' => 'Percentual (%)',
                            'fixed'   => 'Valor fixo (R$)',
                        ])
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('value')
                        ->label(fn (Get $get) => $get('type') === 'percent' ? 'Desconto (%)' : 'Desconto (R$)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->suffix(fn (Get $get) => $get('type') === 'percent' ? '%' : null)
                        ->prefix(fn (Get $get) => $get('type') === 'fixed' ? 'R$' : null)
                        ->required(),

                    Forms\Components\TextInput::make('min_cart_value')
                        ->label('Valor mínimo do pedido (R$)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->prefix('R$')
                        ->helperText('Deixe vazio para sem mínimo.'),
                ]),

            Forms\Components\Section::make('Limites de Uso')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('max_uses')
                        ->label('Limite total de usos')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Deixe vazio para ilimitado.'),

                    Forms\Components\TextInput::make('max_uses_per_customer')
                        ->label('Limite de uso por cliente')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Deixe vazio para ilimitado por cliente.'),
                ]),

            Forms\Components\Section::make('Validade')
                ->columns(2)
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Válido a partir de')
                        ->displayFormat('d/m/Y H:i')
                        ->helperText('Deixe vazio para válido imediatamente.'),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Expira em')
                        ->displayFormat('d/m/Y H:i')
                        ->helperText('Deixe vazio para sem expiração.'),
                ]),

            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Toggle::make('active')
                        ->label('Cupom ativo')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? 'Percentual' : 'Valor fixo')
                    ->badge()
                    ->color(fn ($state) => $state === 'percent' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Desconto')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percent'
                        ? number_format($state, 0) . '%'
                        : 'R$ ' . number_format($state, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('usages_count')
                    ->label('Usos')
                    ->counts('usages')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('max_uses')
                    ->label('Limite')
                    ->formatStateUsing(fn ($state) => $state ?? '∞'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y')
                    ->placeholder('Sem expiração')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ConditionsRelationManager::class,
            RelationManagers\UsagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
