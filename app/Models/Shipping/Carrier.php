<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    protected $fillable = ['name', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function rates(): HasMany
    {
        return $this->hasMany(CarrierShippingRate::class);
    }

    /**
     * Retorna a tarifa que cobre o CEP e o peso informados.
     * Se weight_to for null, não há limite superior de peso.
     */
    public function rateForCepAndWeight(string $cep, float $weight = 0): ?CarrierShippingRate
    {
        return $this->rates()
            ->where('cep_from', '<=', $cep)
            ->where('cep_to', '>=', $cep)
            ->where('weight_from', '<=', $weight)
            ->where(function ($q) use ($weight) {
                $q->whereNull('weight_to')
                  ->orWhere('weight_to', '>=', $weight);
            })
            ->orderByDesc('weight_from') // faixa mais específica primeiro
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
