<?php

namespace App\Models\Cart;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'session_id',
        'coupon_code',
        'coupon_discount',
        'abandoned_cart_sent_at',
    ];

    protected $casts = [
        'coupon_discount'        => 'float',
        'abandoned_cart_sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->unit_price * $item->quantity);
    }

    /** Total já com desconto de cupom (sem frete — o frete é somado no checkout) */
    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - ($this->coupon_discount ?? 0));
    }
}
