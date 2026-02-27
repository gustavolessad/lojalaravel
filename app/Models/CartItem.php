<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity'   => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getOriginalPriceAttribute(): ?float
    {
        if ($this->variant) {
            if ($this->variant->price !== null) {
                return $this->variant->isOnSale() ? (float) $this->variant->price : null;
            }
            return $this->product->isOnSale() ? (float) $this->product->price : null;
        }

        return $this->product->isOnSale() ? (float) $this->product->price : null;
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    public function getNameAttribute(): string
    {
        $name = $this->product->name;
        if ($this->variant) {
            $label = $this->variant->label;
            if ($label) {
                $name .= ' — ' . $label;
            }
        }
        return $name;
    }

    public function getMaxStockAttribute(): int
    {
        if ($this->variant) {
            return (int) ($this->variant->stock ?? 0);
        }

        return (int) ($this->product->stock ?? 0);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->variant) {
            $url = $this->variant->getFirstMediaUrl('variant-cover');
            if ($url) return $url;
        }
        return $this->product->getFirstMediaUrl('cover') ?: '';
    }
}
