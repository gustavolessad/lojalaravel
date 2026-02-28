<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRequestResource\Pages;
use App\Filament\Resources\ReturnRequestResource\RelationManagers;
use App\Models\ReturnRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?string $navigationLabel = 'Trocas e Devoluções';
    protected static ?string $modelLabel      = 'Solicitação';
    protected static ?string $pluralModelLabel = 'Trocas e Devoluções';
    protected static ?int $navigationSort     = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ── Form (edit) ──────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informações da Solicitação')
                ->schema([
                    Forms\Components\Placeholder::make('customer_name')
                        ->label('Cliente')
                        ->content(fn (ReturnRequest $record): string => $record->customer?->display_name ?? '—'),

                    Forms\Components\Placeholder::make('order_number')
                        ->label('Pedido')
                        ->content(fn (ReturnRequest $record): string => '#' . ($record->order?->order_number ?? '—')),

                    Forms\Components\Placeholder::make('type_label')
                        ->label('Tipo')
                        ->content(fn (ReturnRequest $record): string => $record->type_label),

                    Forms\Components\Placeholder::make('reason_label')
                        ->label('Motivo')
                        ->content(fn (ReturnRequest $record): string => $record->reason_label),

                    Forms\Components\Placeholder::make('created_at_fmt')
                        ->label('Data da solicitação')
                        ->content(fn (ReturnRequest $record): string => $record->created_at->format('d/m/Y H:i')),

                    Forms\Components\Placeholder::make('customer_message')
                        ->label('Mensagem do cliente')
                        ->content(fn (ReturnRequest $record): string => $record->message ?? '—')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Gerenciar Solicitação')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(ReturnRequest::statuses())
                        ->required(),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Notas internas / Resposta ao cliente')
                        ->helperText('Esta resposta será exibida ao cliente no painel Minha Conta.')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // ── Table ──────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width(60),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (ReturnRequest $r) => $r->customer?->email),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Pedido')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => '#' . $state),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (ReturnRequest $r) => $r->type_label)
                    ->color(fn (string $state) => match($state) {
                        'exchange' => 'purple',
                        'return'   => 'orange',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->formatStateUsing(fn (ReturnRequest $r) => $r->reason_label)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (ReturnRequest $r) => $r->status_label)
                    ->color(fn (string $state) => match($state) {
                        'pending'     => 'warning',
                        'approved'    => 'success',
                        'rejected'    => 'danger',
                        'in_progress' => 'info',
                        'completed'   => 'gray',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Itens')
                    ->counts('items')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(ReturnRequest::statuses()),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'exchange' => 'Troca',
                        'return'   => 'Devolução',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Gerenciar'),
            ])
            ->bulkActions([]);
    }

    // ── Relation Managers ──────────────────────────────────────────────

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    // ── Pages ──────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnRequests::route('/'),
            'edit'  => Pages\EditReturnRequest::route('/{record}/edit'),
        ];
    }
}
