<?php

namespace App\Filament\Resources\CarrierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship   = 'rates';
    protected static ?string $title         = 'Tabela de Frete por Faixa de CEP e Peso';
    protected static ?string $modelLabel    = 'Faixa';
    protected static ?string $pluralModelLabel = 'Faixas';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Faixa de CEP')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('cep_from')
                        ->label('CEP Inicial')
                        ->mask('99999-999')
                        ->placeholder('00000-000')
                        ->required()
                        ->maxLength(9)
                        ->helperText('Ex: 88750-000'),

                    Forms\Components\TextInput::make('cep_to')
                        ->label('CEP Final')
                        ->mask('99999-999')
                        ->placeholder('99999-999')
                        ->required()
                        ->maxLength(9)
                        ->helperText('Ex: 88870-999'),
                ]),

            Forms\Components\Section::make('Faixa de Peso (kg)')
                ->columns(2)
                ->description('Deixe em branco para aplicar a qualquer peso.')
                ->schema([
                    Forms\Components\TextInput::make('weight_from')
                        ->label('Peso mínimo (kg)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->suffix('kg')
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('weight_to')
                        ->label('Peso máximo (kg)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->suffix('kg')
                        ->helperText('Deixe vazio para sem limite.'),
                ]),

            Forms\Components\Section::make('Valor e Prazo')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Preço (R$)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->prefix('R$')
                        ->required(),

                    Forms\Components\TextInput::make('days')
                        ->label('Prazo (dias úteis)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(90)
                        ->suffix('dias')
                        ->required(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cep_from')
            ->columns([
                Tables\Columns\TextColumn::make('cep_from')
                    ->label('CEP Inicial')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5) . '-' . substr($state, 5)),

                Tables\Columns\TextColumn::make('cep_to')
                    ->label('CEP Final')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5) . '-' . substr($state, 5)),

                Tables\Columns\TextColumn::make('weight_from')
                    ->label('Peso mín.')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.') . ' kg')
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight_to')
                    ->label('Peso máx.')
                    ->formatStateUsing(fn ($state) => $state !== null
                        ? number_format($state, 2, ',', '.') . ' kg'
                        : '∞')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('days')
                    ->label('Prazo')
                    ->suffix(' dias')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('cep_from');
    }
}
