<?php

namespace App\Filament\Resources;

use App\Events\ReviewApproved;
use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\Catalog\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon   = 'heroicon-o-star';
    protected static ?string $navigationGroup  = 'Catálogo';
    protected static ?string $navigationLabel  = 'Avaliações';
    protected static ?string $modelLabel       = 'Avaliação';
    protected static ?string $pluralModelLabel = 'Avaliações';
    protected static ?int $navigationSort      = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Avaliação')
                ->schema([
                    Forms\Components\Placeholder::make('customer_name')
                        ->label('Cliente')
                        ->content(fn (ProductReview $record) => $record->customer?->display_name ?? '—'),

                    Forms\Components\Placeholder::make('product_name')
                        ->label('Produto')
                        ->content(fn (ProductReview $record) => $record->product?->name ?? '—'),

                    Forms\Components\Placeholder::make('rating_display')
                        ->label('Nota')
                        ->content(fn (ProductReview $record) => str_repeat('★', $record->rating) . str_repeat('☆', 5 - $record->rating) . " ({$record->rating}/5)"),

                    Forms\Components\Placeholder::make('created_at_fmt')
                        ->label('Data')
                        ->content(fn (ProductReview $record) => $record->created_at->format('d/m/Y H:i')),

                    Forms\Components\Placeholder::make('title_display')
                        ->label('Título')
                        ->content(fn (ProductReview $record) => $record->title ?? '—')
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('comment_display')
                        ->label('Comentário')
                        ->content(fn (ProductReview $record) => $record->comment)
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('photos_display')
                        ->label('Fotos')
                        ->content(function (ProductReview $record) {
                            $media = $record->getMedia('photos');
                            if ($media->isEmpty()) {
                                return '—';
                            }

                            $html = '<div class="flex gap-2 flex-wrap">';
                            foreach ($media as $m) {
                                $thumb = $m->getUrl('thumb');
                                $full  = $m->getUrl('optimized') ?: $m->getUrl();
                                $html .= "<a href=\"{$full}\" target=\"_blank\"><img src=\"{$thumb}\" class=\"w-20 h-20 object-cover rounded-lg\"></a>";
                            }
                            $html .= '</div>';

                            return new HtmlString($html);
                        })
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Moderação')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(ProductReview::statuses())
                        ->required(),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Notas internas')
                        ->helperText('Visível apenas para administradores.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width(60),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Nota')
                    ->formatStateUsing(fn (int $state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ProductReview $record) => $record->status_label)
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(ProductReview::statuses()),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('Nota')
                    ->options([1 => '1 ★', 2 => '2 ★', 3 => '3 ★', 4 => '4 ★', 5 => '5 ★']),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Moderar'),

                Tables\Actions\Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ProductReview $record) => $record->status !== 'approved')
                    ->action(function (ProductReview $record) {
                        $record->update(['status' => 'approved']);
                        ReviewApproved::dispatch($record);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ProductReview $record) => $record->status !== 'rejected')
                    ->action(function (ProductReview $record) {
                        $record->update(['status' => 'rejected']);
                        $record->product->recalculateRating();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_approve')
                    ->label('Aprovar selecionadas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $productIds = [];
                        foreach ($records as $review) {
                            $review->update(['status' => 'approved']);
                            $productIds[] = $review->product_id;
                        }

                        \App\Models\Catalog\Product::whereIn('id', array_unique($productIds))
                            ->each(fn ($p) => $p->recalculateRating());
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReviews::route('/'),
            'edit'  => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
