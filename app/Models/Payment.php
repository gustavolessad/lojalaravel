<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_payment_id',
        'method',
        'status',
        'amount',
        'installments',
        'installment_value',
        'error_message',
        'data',
        'confirmed_at',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'installment_value' => 'decimal:2',
        'data'              => 'array',
        'confirmed_at'      => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Status helpers ────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
    public function isRefunded(): bool  { return $this->status === 'refunded'; }

    // ── Label accessors ───────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Aguardando',
            'confirmed' => 'Confirmado',
            'failed'    => 'Falhou',
            'refunded'  => 'Reembolsado',
            default     => $this->status,
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'pix'         => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'boleto'      => 'Boleto',
            default       => $this->method,
        };
    }
}
