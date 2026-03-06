<?php

namespace App\Models\Shipping;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreeShippingRuleCondition extends Model
{
    protected $fillable = ['rule_id', 'type', 'item_id'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FreeShippingRule::class);
    }

    /**
     * Retorna o nome legível do item (categoria ou produto) para exibição.
     */
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
