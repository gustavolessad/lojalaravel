<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponCondition extends Model
{
    protected $fillable = ['coupon_id', 'type', 'item_id'];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getItemNameAttribute(): string
    {
        if (str_contains($this->type, 'category')) {
            return Category::find($this->item_id)?->name ?? "Categoria #{$this->item_id}";
        }

        return Product::find($this->item_id)?->name ?? "Produto #{$this->item_id}";
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'include_category' => 'Incluir categoria',
            'exclude_category' => 'Excluir categoria',
            'include_product'  => 'Incluir produto',
            'exclude_product'  => 'Excluir produto',
            default            => $this->type,
        };
    }
}
