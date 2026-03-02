<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationGroup = 'Vendas';
    protected static ?string $navigationLabel = 'Pagamentos';
    protected static ?string $pluralModelLabel = 'pagamentos';
    protected static ?string $modelLabel        = 'pagamento';
    protected static ?string $navigationIcon    = 'heroicon-o-credit-card';
    protected static ?int    $navigationSort    = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn (Payment $r) => OrderResource::getUrl('view', ['record' => $r->order_id])),

                Tables\Columns\TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão',
                        'boleto'      => 'Boleto',
                        default       => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pix'         => 'success',
                        'credit_card' => 'info',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'   => 'Aguardando',
                        'confirmed' => 'Confirmado',
                        'failed'    => 'Falhou',
                        'refunded'  => 'Reembolsado',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'   => 'warning',
                        'confirmed' => 'success',
                        'failed'    => 'danger',
                        'refunded'  => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('installments')
                    ->label('Parcelas')
                    ->formatStateUsing(fn ($state) => $state > 1 ? "{$state}×" : '1×'),

                Tables\Columns\TextColumn::make('gateway_payment_id')
                    ->label('ID Gateway')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Erro')
                    ->limit(55)
                    ->placeholder('—')
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tentativa em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Confirmado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Aguardando',
                        'confirmed' => 'Confirmado',
                        'failed'    => 'Falhou',
                        'refunded'  => 'Reembolsado',
                    ]),
                Tables\Filters\SelectFilter::make('method')
                    ->label('Método')
                    ->options([
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão de Crédito',
                        'boleto'      => 'Boleto',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_pedido')
                    ->label('Ver pedido')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn (Payment $r) => OrderResource::getUrl('view', ['record' => $r->order_id])),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Pagamento')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order.order_number')->label('Pedido'),
                        TextEntry::make('gateway_payment_id')->label('ID Gateway')->placeholder('—')->copyable(),
                        TextEntry::make('method_label')->label('Método'),
                        TextEntry::make('status_label')->label('Status')
                            ->badge()
                            ->color(fn (Payment $r) => match ($r->status) {
                                'pending'   => 'warning',
                                'confirmed' => 'success',
                                'failed'    => 'danger',
                                default     => 'gray',
                            }),
                        TextEntry::make('amount')->label('Valor')->money('BRL'),
                        TextEntry::make('installments')->label('Parcelas')
                            ->formatStateUsing(fn ($state) => $state > 1 ? "{$state}×" : '1×'),
                        TextEntry::make('error_message')->label('Mensagem de erro')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('created_at')->label('Tentativa em')->dateTime('d/m/Y H:i'),
                        TextEntry::make('confirmed_at')->label('Confirmado em')->dateTime('d/m/Y H:i')->placeholder('—'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view'  => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
