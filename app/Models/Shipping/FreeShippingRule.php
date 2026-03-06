<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreeShippingRule extends Model
{
    protected $fillable = [
        'name',
        'cep_from',
        'cep_to',
        'min_cart_value',
        'active',
    ];

    protected $casts = [
        'min_cart_value' => 'float',
        'active'         => 'boolean',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(FreeShippingRuleCondition::class, 'rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /** Mutators para remover caracteres não-numéricos dos CEPs */
    public function setCepFromAttribute(?string $value): void
    {
        $this->attributes['cep_from'] = $value ? preg_replace('/\D/', '', $value) : null;
    }

    public function setCepToAttribute(?string $value): void
    {
        $this->attributes['cep_to'] = $value ? preg_replace('/\D/', '', $value) : null;
    }
}
