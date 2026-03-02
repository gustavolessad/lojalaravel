<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Label e cor por tipo ──────────────────────────────────────────────

    public function getLabelAttribute(): string
    {
        return match ($this->type) {
            'order_created'          => 'Pedido criado',
            'payment_attempted'      => 'Tentativa de pagamento',
            'payment_failed'         => 'Pagamento falhou',
            'payment_confirmed'      => 'Pagamento confirmado',
            'payment_method_changed' => 'Método alterado',
            'order_shipped'          => 'Pedido enviado',
            'order_delivered'        => 'Pedido entregue',
            'order_cancelled'        => 'Pedido cancelado',
            default                  => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getDotColorAttribute(): string
    {
        return match ($this->type) {
            'order_created'          => 'bg-blue-400',
            'payment_attempted'      => 'bg-amber-400',
            'payment_failed'         => 'bg-red-400',
            'payment_confirmed'      => 'bg-emerald-500',
            'payment_method_changed' => 'bg-purple-400',
            'order_shipped'          => 'bg-purple-500',
            'order_delivered'        => 'bg-green-500',
            'order_cancelled'        => 'bg-gray-400',
            default                  => 'bg-gray-400',
        };
    }

    public function getTextColorAttribute(): string
    {
        return match ($this->type) {
            'order_created'          => 'text-blue-700',
            'payment_attempted'      => 'text-amber-700',
            'payment_failed'         => 'text-red-600',
            'payment_confirmed'      => 'text-emerald-700',
            'payment_method_changed' => 'text-purple-700',
            'order_shipped'          => 'text-purple-700',
            'order_delivered'        => 'text-green-700',
            'order_cancelled'        => 'text-gray-600',
            default                  => 'text-gray-600',
        };
    }
}
