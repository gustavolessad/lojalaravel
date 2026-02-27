<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockNotificationResource\Pages;
use App\Models\StockNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockNotificationResource extends Resource
{
    protected static ?string $model = StockNotification::class;

    protected static ?string $navigationIcon  = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup  = 'Marketing';
    protected static ?string $navigationLabel = 'Avise-me';
    protected static ?string $modelLabel      = 'Aviso de Estoque';
    protected static ?string $pluralModelLabel = 'Avisos de Estoque';
    protected static ?int    $navigationSort  = 3;

    /** Badge vermelho com a contagem de pendentes no menu lateral. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereNull('notified_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /** Eager load para evitar N+1 na tabela. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'variant.attributeValues']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('variant_label')
                    ->label('Variante')
                    ->getStateUsing(fn (StockNotification $record): string =>
                        $record->variant?->label ?: '—'
                    )
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('E-mail copiado!'),

                Tables\Columns\IconColumn::make('notified_at')
                    ->label('Notificado')
                    ->boolean()
                    ->getStateUsing(fn (StockNotification $record): bool =>
                        $record->notified_at !== null
                    )
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notified_at')
                    ->label('Notificado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('pending')
                    ->label('Somente pendentes')
                    ->query(fn (Builder $query) => $query->whereNull('notified_at'))
                    ->default(),

                Tables\Filters\Filter::make('notified')
                    ->label('Somente notificados')
                    ->query(fn (Builder $query) => $query->whereNotNull('notified_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_notified')
                    ->label('Marcar como notificado')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (StockNotification $record): bool => $record->notified_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar notificação')
                    ->modalDescription('Isso marcará o aviso como enviado. O e-mail não é enviado automaticamente ainda (Fase 7).')
                    ->action(fn (StockNotification $record) => $record->update(['notified_at' => now()])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_all_notified')
                        ->label('Marcar como notificados')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['notified_at' => now()])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockNotifications::route('/'),
        ];
    }
}
