<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Mail\OrderShipped;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?string $navigationLabel = 'Pedidos';
    protected static ?string $modelLabel      = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';
    protected static ?int $navigationSort     = 1;

    // Badge com novos pedidos pendentes desde a última visita
    public static function getNavigationBadge(): ?string
    {
        $since = cache('admin_orders_viewed_' . auth()->id());
        $query = Order::where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled');
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        $count = $query->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Pedido')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('buyer_name')
                    ->label('Cliente')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                              ->orWhere('guest_name', 'like', "%{$search}%");
                        });
                    })
                    ->limit(30),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->status) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'paid'       => 'success',
                        'shipped'    => 'info',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        'refunded'   => 'gray',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_method_label')
                    ->label('Pagamento')
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->payment_method) {
                        'pix'         => 'success',
                        'credit_card' => 'info',
                        'boleto'      => 'warning',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status_label')
                    ->label('Pag. Status')
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->payment_status) {
                        'paid'     => 'success',
                        'pending'  => 'warning',
                        'failed'   => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipping_city')
                    ->label('Cidade')
                    ->formatStateUsing(fn (Order $record): string => "{$record->shipping_city}/{$record->shipping_state}"),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'    => 'Aguardando pagamento',
                        'processing' => 'Em processamento',
                        'paid'       => 'Pago',
                        'shipped'    => 'Enviado',
                        'delivered'  => 'Entregue',
                        'cancelled'  => 'Cancelado',
                        'refunded'   => 'Reembolsado',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Forma de pagamento')
                    ->options([
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão',
                        'boleto'      => 'Boleto',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status pagamento')
                    ->options([
                        'pending'  => 'Pendente',
                        'paid'     => 'Pago',
                        'failed'   => 'Falhou',
                        'refunded' => 'Reembolsado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar Pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->payment_status === 'pending' && $record->status !== 'cancelled')
                    ->action(fn (Order $record) => $record->update([
                        'payment_status' => 'paid',
                        'status'         => 'processing',
                        'paid_at'        => now(),
                    ])),

                Tables\Actions\Action::make('mark_shipped')
                    ->label('Marcar Enviado')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Order $record) => in_array($record->status, ['paid', 'processing']))
                    ->form([
                        Forms\Components\TextInput::make('tracking_code')
                            ->label('Código de rastreamento')
                            ->placeholder('Ex: BR123456789BR')
                            ->nullable(),
                        Forms\Components\TextInput::make('tracking_url')
                            ->label('Link de rastreamento (opcional)')
                            ->url()
                            ->placeholder('https://...')
                            ->nullable(),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update([
                            'status'        => 'shipped',
                            'shipped_at'    => now(),
                            'tracking_code' => $data['tracking_code'] ?: null,
                            'tracking_url'  => $data['tracking_url'] ?: null,
                        ]);

                        // Envia e-mail de envio para o comprador (via fila)
                        if ($record->buyer_email && $record->buyer_email !== '—') {
                            Mail::to($record->buyer_email)->queue(new OrderShipped($record));
                        }
                    }),

                Tables\Actions\Action::make('mark_delivered')
                    ->label('Marcar Entregue')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'shipped')
                    ->action(fn (Order $record) => $record->update([
                        'status'       => 'delivered',
                        'delivered_at' => now(),
                    ])),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => in_array($record->status, ['pending', 'processing']))
                    ->action(fn (Order $record) => $record->update([
                        'status'       => 'cancelled',
                        'cancelled_at' => now(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── Infolist (visualização) ────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ── Linha 1: cabeçalho do pedido ─────────────────────────
                Infolists\Components\Section::make('Informações do Pedido')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Número do Pedido')
                            ->weight('bold')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('buyer_name')
                            ->label('Cliente')
                            ->color('primary')
                            ->url(fn (Order $record): string => \App\Filament\Resources\CustomerResource::getUrl('edit', ['record' => $record->customer_id]))
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('buyer_email')
                            ->label('E-mail'),

                        Infolists\Components\TextEntry::make('status_label')
                            ->label('Status do Pedido')
                            ->badge()
                            ->color(fn (Order $record): string => match ($record->status) {
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'paid'       => 'success',
                                'shipped'    => 'info',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                'refunded'   => 'gray',
                                default      => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('payment_status_label')
                            ->label('Status do Pagamento')
                            ->badge()
                            ->color(fn (Order $record): string => match ($record->payment_status) {
                                'paid'     => 'success',
                                'pending'  => 'warning',
                                'failed'   => 'danger',
                                'refunded' => 'gray',
                                default    => 'gray',
                            }),
                    ]),

                // ── Itens do pedido ───────────────────────────────────────
                Infolists\Components\Section::make('Produtos')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->columns(5)
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('Produto')
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('variant_label')
                                    ->label('Variação')
                                    ->default('—'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qtd'),

                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Preço Unit.')
                                    ->money('BRL'),

                                Infolists\Components\TextEntry::make('total')
                                    ->label('Subtotal')
                                    ->money('BRL')
                                    ->weight('bold'),
                            ]),
                    ]),

                // ── Valores ───────────────────────────────────────────────
                Infolists\Components\Section::make('Valores')
                    ->schema([
                        Infolists\Components\View::make('filament.infolists.order-values')
                            ->columnSpanFull(),
                    ]),

                // ── Entrega ───────────────────────────────────────────────
                Infolists\Components\Section::make('Endereço de Entrega')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('shipping_name')
                            ->label('Destinatário'),

                        Infolists\Components\TextEntry::make('shipping_zip')
                            ->label('CEP'),

                        Infolists\Components\TextEntry::make('shipping_street')
                            ->label('Logradouro'),

                        Infolists\Components\TextEntry::make('shipping_number')
                            ->label('Número'),

                        Infolists\Components\TextEntry::make('shipping_complement')
                            ->label('Complemento')
                            ->default('—'),

                        Infolists\Components\TextEntry::make('shipping_district')
                            ->label('Bairro'),

                        Infolists\Components\TextEntry::make('shipping_city')
                            ->label('Cidade'),

                        Infolists\Components\TextEntry::make('shipping_state')
                            ->label('Estado'),

                        Infolists\Components\TextEntry::make('shipping_method')
                            ->label('Modalidade'),

                        Infolists\Components\TextEntry::make('shipping_days')
                            ->label('Prazo (dias úteis)'),

                        Infolists\Components\TextEntry::make('tracking_code')
                            ->label('Código de rastreamento')
                            ->copyable()
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('tracking_url')
                            ->label('Link de rastreamento')
                            ->url(fn (Order $record): ?string => $record->tracking_url ?: null)
                            ->openUrlInNewTab()
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('shipped_at')
                            ->label('Enviado em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('delivered_at')
                            ->label('Entregue em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ]),

                // ── Observações ───────────────────────────────────────────
                Infolists\Components\Section::make('Observações')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->default('Sem observações.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ── Form (edição de status e observações) ─────────────────────────────

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status do Pedido')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'    => 'Aguardando pagamento',
                                'processing' => 'Em processamento',
                                'paid'       => 'Pago',
                                'shipped'    => 'Enviado',
                                'delivered'  => 'Entregue',
                                'cancelled'  => 'Cancelado',
                                'refunded'   => 'Reembolsado',
                            ])
                            ->required(),

                        Forms\Components\Select::make('payment_status')
                            ->label('Status do Pagamento')
                            ->options([
                                'pending'  => 'Aguardando',
                                'paid'     => 'Pago',
                                'failed'   => 'Falhou',
                                'refunded' => 'Reembolsado',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Pago em')
                            ->displayFormat('d/m/Y H:i')
                            ->nullable(),

                        Forms\Components\TextInput::make('tracking_code')
                            ->label('Código de rastreamento')
                            ->placeholder('Ex: BR123456789BR')
                            ->nullable(),

                        Forms\Components\TextInput::make('tracking_url')
                            ->label('Link de rastreamento')
                            ->url()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->label('Enviado em')
                            ->displayFormat('d/m/Y H:i')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Entregue em')
                            ->displayFormat('d/m/Y H:i')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('Cancelado em')
                            ->displayFormat('d/m/Y H:i')
                            ->nullable(),
                    ]),

                Forms\Components\Section::make('Observações Internas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->nullable(),
                    ]),

                // Informações somente leitura
                Forms\Components\Section::make('Resumo')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('order_number')->label('Pedido')->disabled(),
                        Forms\Components\TextInput::make('buyer_name')
                            ->label('Cliente')
                            ->disabled()
                            ->formatStateUsing(fn ($record) => $record?->customer?->display_name ?? $record?->guest_name ?? '—'),
                        Forms\Components\TextInput::make('total')->label('Total')->prefix('R$')->disabled(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\OrderEventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
