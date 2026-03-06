<?php

namespace App\Filament\Resources\CouponResource\RelationManagers;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship      = 'conditions';
    protected static ?string $title            = 'Condições (inclusão / exclusão)';
    protected static ?string $modelLabel       = 'Condição';
    protected static ?string $pluralModelLabel = 'Condições';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Tipo de condição')
                ->options([
                    'exclude_category' => '🚫 Excluir categoria — NÃO aplicar se tiver produto desta categoria',
                    'exclude_product'  => '🚫 Excluir produto — NÃO aplicar se este produto estiver no carrinho',
                    'include_category' => '✅ Incluir categoria — Aplicar SOMENTE para esta categoria',
                    'include_product'  => '✅ Incluir produto — Aplicar SOMENTE para este produto',
                ])
                ->required()
                ->live()
                ->columnSpanFull(),

            Forms\Components\Select::make('item_id')
                ->label(fn (Get $get) => str_contains((string) $get('type'), 'category') ? 'Categoria' : 'Produto')
                ->options(function (Get $get) {
                    $type = (string) $get('type');
                    if (str_contains($type, 'category')) {
                        return Category::orderBy('name')->pluck('name', 'id');
                    }
                    return Product::orderBy('name')->pluck('name', 'id');
                })
                ->searchable()
                ->required()
                ->visible(fn (Get $get) => ! empty($get('type')))
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'exclude_category' => '🚫 Excluir categoria',
                        'exclude_product'  => '🚫 Excluir produto',
                        'include_category' => '✅ Incluir categoria',
                        'include_product'  => '✅ Incluir produto',
                        default            => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => str_starts_with($state, 'exclude') ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item')
                    ->getStateUsing(fn ($record) => $record->item_name_attribute),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
