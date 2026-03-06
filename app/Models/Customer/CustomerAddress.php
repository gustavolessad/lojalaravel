<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'label',
        'cep',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'country',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            "{$this->street}, {$this->number}",
            $this->complement,
            $this->district,
            "{$this->city} - {$this->state}",
            $this->cep,
        ];

        return implode(', ', array_filter($parts));
    }
}
