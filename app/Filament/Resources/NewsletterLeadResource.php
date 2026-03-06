<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterLeadResource\Pages;
use App\Models\Marketing\NewsletterLead;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterLeadResource extends Resource
{
    protected static ?string $model = NewsletterLead::class;

    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Newsletter';
    protected static ?string $modelLabel      = 'Lead';
    protected static ?string $pluralModelLabel = 'Leads';
    protected static ?int    $navigationSort  = 2;

    public static function getNavigationBadge(): ?string
    {
        $since = cache('admin_newsletter_viewed_' . auth()->id());
        $query = static::getModel()::query();
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        $count = $query->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterLeads::route('/'),
        ];
    }
}
