<?php

namespace App\Models\Sales;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'status',
        'reason',
        'message',
        'admin_notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'Aguardando',
            'approved'    => 'Aprovado',
            'rejected'    => 'Recusado',
            'in_progress' => 'Em andamento',
            'completed'   => 'Concluído',
            default       => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'exchange' => 'Troca',
            'return'   => 'Devolução',
            default    => $this->type,
        };
    }

    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'defective'     => 'Produto com defeito',
            'wrong_product' => 'Produto diferente do pedido',
            'damaged'       => 'Produto danificado na entrega',
            'regret'        => 'Arrependimento / não gostei',
            'wrong_size'    => 'Tamanho errado',
            'wrong_color'   => 'Cor diferente da esperada',
            'other'         => 'Outro',
            default         => $this->reason,
        };
    }

    public static function reasons(): array
    {
        return [
            'defective'     => 'Produto com defeito',
            'wrong_product' => 'Produto diferente do pedido',
            'damaged'       => 'Produto danificado na entrega',
            'regret'        => 'Arrependimento / não gostei',
            'wrong_size'    => 'Tamanho errado',
            'wrong_color'   => 'Cor diferente da esperada',
            'other'         => 'Outro motivo',
        ];
    }

    public static function statuses(): array
    {
        return [
            'pending'     => 'Aguardando',
            'approved'    => 'Aprovado',
            'rejected'    => 'Recusado',
            'in_progress' => 'Em andamento',
            'completed'   => 'Concluído',
        ];
    }
}
