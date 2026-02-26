<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Logistica;
use App\Filament\Resources\FreeShippingRuleResource\Pages;
use App\Filament\Resources\FreeShippingRuleResource\RelationManagers;
use App\Models\FreeShippingRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FreeShippingRuleResource extends Resource
{
    protected static ?string $model = FreeShippingRule::class;

    protected static ?string $cluster          = Logistica::class;
    protected static ?string $navigationIcon   = 'heroicon-o-gift';
    protected static ?string $navigationLabel  = 'Frete Grátis';
    protected static ?string $modelLabel       = 'Regra de Frete Grátis';
    protected static ?string $pluralModelLabel = 'Regras de Frete Grátis';
    protected static ?int    $navigationSort   = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome da regra')
                        ->placeholder('Ex: Frete grátis SC acima de R$ 500')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('active')
                        ->label('Regra ativa')
                        ->default(true),
                ]),

            Forms\Components\Section::make('Faixa de CEP')
                ->description('Deixe em branco para aplicar a qualquer CEP.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('cep_from')
                        ->label('CEP Inicial')
                        ->mask('99999-999')
                        ->placeholder('00000-000')
                        ->maxLength(9),

                    Forms\Components\TextInput::make('cep_to')
                        ->label('CEP Final')
                        ->mask('99999-999')
                        ->placeholder('99999-999')
                        ->maxLength(9),
                ]),

            Forms\Components\Section::make('Valor mínimo do carrinho')
                ->description('Valor total dos itens para ativar o frete grátis. Deixe em branco para sem mínimo.')
                ->schema([
                    Forms\Components\TextInput::make('min_cart_value')
                        ->label('Valor mínimo (R$)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->prefix('R$')
                        ->placeholder('Ex: 500.00'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cep_from')
                    ->label('CEP Inicial')
                    ->formatStateUsing(fn ($state) => $state
                        ? substr($state, 0, 5) . '-' . substr($state, 5)
                        : 'Qualquer')
                    ->placeholder('Qualquer'),

                Tables\Columns\TextColumn::make('cep_to')
                    ->label('CEP Final')
                    ->formatStateUsing(fn ($state) => $state
                        ? substr($state, 0, 5) . '-' . substr($state, 5)
                        : 'Qualquer')
                    ->placeholder('Qualquer'),

                Tables\Columns\TextColumn::make('min_cart_value')
                    ->label('Valor mínimo')
                    ->money('BRL')
                    ->placeholder('Sem mínimo'),

                Tables\Columns\TextColumn::make('conditions_count')
                    ->label('Condições')
                    ->counts('conditions')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->trueLabel('Apenas ativas')
                    ->falseLabel('Apenas inativas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ConditionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFreeShippingRules::route('/'),
            'create' => Pages\CreateFreeShippingRule::route('/create'),
            'edit'   => Pages\EditFreeShippingRule::route('/{record}/edit'),
        ];
    }
}
