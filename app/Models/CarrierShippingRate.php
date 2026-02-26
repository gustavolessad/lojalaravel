<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierShippingRate extends Model
{
    protected $fillable = [
        'carrier_id',
        'cep_from',
        'cep_to',
        'weight_from',
        'weight_to',
        'price',
        'days',
    ];

    protected $casts = [
        'weight_from' => 'float',
        'weight_to'   => 'float',
        'price'       => 'float',
        'days'        => 'integer',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Mutator: armazena somente os dígitos do CEP.
     */
    public function setCepFromAttribute(string $value): void
    {
        $this->attributes['cep_from'] = preg_replace('/\D/', '', $value);
    }

    public function setCepToAttribute(string $value): void
    {
        $this->attributes['cep_to'] = preg_replace('/\D/', '', $value);
    }
}
