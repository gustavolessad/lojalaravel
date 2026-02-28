<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LowStockWidget extends Widget
{
    protected static ?int $sort = 5;

    protected static string $view = 'filament.widgets.low-stock';

    protected int | string | array $columnSpan = 1;

    public function getLowStockItems(): Collection
    {
        // Produtos simples com estoque baixo ou zerado
        $products = Product::where('active', true)
            ->where('type', 'simple')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(100)
            ->get(['id', 'name', 'stock'])
            ->map(fn ($p) => [
                'name'    => $p->name,
                'variant' => null,
                'stock'   => (int) $p->stock,
                'url'     => \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $p->id]),
            ]);

        // Variantes com estoque baixo ou zerado
        $variants = ProductVariant::where('active', true)
            ->where('stock', '<=', 5)
            ->with('product:id,name')
            ->orderBy('stock')
            ->limit(100)
            ->get(['id', 'product_id', 'sku', 'stock'])
            ->map(fn ($v) => [
                'name'    => $v->product->name ?? '—',
                'variant' => $v->sku,
                'stock'   => (int) $v->stock,
                'url'     => \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $v->product_id]),
            ]);

        return $products->concat($variants)->sortBy('stock')->values();
    }
}
