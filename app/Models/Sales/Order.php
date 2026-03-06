<?php

namespace App\Models\Sales;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{

    protected $fillable = [
        'order_number',
        'customer_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'payment_status',
        'payment_method',
        'payment_id',
        'payment_data',
        'shipping_name',
        'shipping_phone',
        'shipping_zip',
        'shipping_street',
        'shipping_number',
        'shipping_complement',
        'shipping_district',
        'shipping_city',
        'shipping_state',
        'shipping_method',
        'shipping_days',
        'tracking_code',
        'tracking_url',
        'shipping_cost',
        'subtotal',
        'discount',
        'pix_discount',
        'coupon_code',
        'total',
        'notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_data' => 'array',
            'subtotal'     => 'decimal:2',
            'discount'     => 'decimal:2',
        'pix_discount'  => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total'        => 'decimal:2',
            'paid_at'      => 'datetime',
            'shipped_at'   => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ── Relacionamentos ───────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function orderEvents(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('created_at');
    }

    // ── Geração do número do pedido ───────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year = now()->format('Y');

        $lastNumber = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->value('order_number');

        $seq = $lastNumber
            ? ((int) str_replace("{$year}-", '', $lastNumber)) + 1
            : 1;

        return sprintf('%s-%05d', $year, $seq);
    }

    // ── Helpers de status ─────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isPaid(): bool      { return $this->payment_status === 'paid'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isShipped(): bool   { return $this->status === 'shipped'; }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Aguardando pagamento',
            'processing' => 'Em processamento',
            'paid'       => 'Pago',
            'shipped'    => 'Enviado',
            'delivered'  => 'Entregue',
            'cancelled'  => 'Cancelado',
            'refunded'   => 'Reembolsado',
            default      => ucfirst($this->status),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending'  => 'Aguardando',
            'paid'     => 'Pago',
            'failed'   => 'Falhou',
            'refunded' => 'Reembolsado',
            default    => ucfirst($this->payment_status),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'pix'         => 'PIX',
            'credit_card' => 'Cartão de crédito',
            'boleto'      => 'Boleto',
            default       => '—',
        };
    }

    // Nome do comprador (logado ou guest)
    public function getBuyerNameAttribute(): string
    {
        return $this->customer?->display_name ?? $this->guest_name ?? '—';
    }

    public function getBuyerEmailAttribute(): string
    {
        return $this->customer?->email ?? $this->guest_email ?? '—';
    }

    // Endereço de entrega formatado
    public function getShippingAddressFormattedAttribute(): string
    {
        $line = "{$this->shipping_street}, {$this->shipping_number}";
        if ($this->shipping_complement) {
            $line .= " — {$this->shipping_complement}";
        }
        return "{$line}, {$this->shipping_district}, {$this->shipping_city}/{$this->shipping_state} — {$this->shipping_zip}";
    }
}
