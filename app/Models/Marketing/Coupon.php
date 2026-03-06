<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_cart_value',
        'max_uses',
        'max_uses_per_customer',
        'starts_at',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'value'                 => 'float',
        'min_cart_value'        => 'float',
        'max_uses'              => 'integer',
        'max_uses_per_customer' => 'integer',
        'starts_at'             => 'datetime',
        'expires_at'            => 'datetime',
        'active'                => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(CouponCondition::class);
    }

    /** Mutator: armazena código sempre em maiúsculas */
    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /** Contagem de usos totais */
    public function getUsesCountAttribute(): int
    {
        return $this->usages()->count();
    }
}
