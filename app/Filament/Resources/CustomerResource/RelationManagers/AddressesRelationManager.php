<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Endereços';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')
                ->label('Identificação')
                ->placeholder('Casa, Trabalho...')
                ->maxLength(50),

            Forms\Components\TextInput::make('cep')
                ->label('CEP')
                ->mask('99999-999')
                ->required()
                ->maxLength(9),

            Forms\Components\TextInput::make('street')
                ->label('Logradouro')
                ->required()
                ->maxLength(255)
                ->columnSpan(2),

            Forms\Components\TextInput::make('number')
                ->label('Número')
                ->required()
                ->maxLength(20),

            Forms\Components\TextInput::make('complement')
                ->label('Complemento')
                ->maxLength(100),

            Forms\Components\TextInput::make('district')
                ->label('Bairro')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('city')
                ->label('Cidade')
                ->required()
                ->maxLength(100),

            Forms\Components\Select::make('state')
                ->label('Estado')
                ->options(array_combine(
                    $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'],
                    $ufs
                ))
                ->required()
                ->searchable(),

            Forms\Components\Hidden::make('country')->default('BR'),

            Forms\Components\Toggle::make('is_default')
                ->label('Endereço padrão')
                ->columnSpanFull(),
        ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Identificação')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('street')
                    ->label('Endereço')
                    ->description(fn ($record) => "{$record->district} — {$record->city}/{$record->state}"),

                Tables\Columns\TextColumn::make('cep')
                    ->label('CEP'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Novo endereço'),
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
}
