<?php

namespace App\Livewire\Account;

use App\Models\Sales\Order;
use App\Models\Sales\ReturnRequest;
use App\Models\User;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ReturnRequestManager extends Component
{
    public string $mode = 'list'; // 'list' | 'create'
    public int $step = 1;

    // Step 1
    public ?int $selectedOrderId = null;

    // Step 2
    public array $selectedItemIds = [];
    public array $itemQuantities  = [];

    // Step 3
    public string $type    = 'return';
    public string $reason  = '';
    public string $message = '';

    public function startNew(): void
    {
        $this->reset(['selectedOrderId', 'selectedItemIds', 'itemQuantities', 'reason', 'message']);
        $this->type = 'return';
        $this->step = 1;
        $this->mode = 'create';
        $this->resetErrorBag();
    }

    public function cancelCreate(): void
    {
        $this->mode = 'list';
        $this->resetErrorBag();
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->selectedItemIds = [];
        $this->itemQuantities  = [];
        $this->resetErrorBag();
        $this->step = 2;
    }

    public function backToStep(int $step): void
    {
        $this->step = $step;
        $this->resetErrorBag();
    }

    public function goToStep3(): void
    {
        if (empty($this->selectedItemIds)) {
            $this->addError('items', 'Selecione pelo menos 1 produto.');
            return;
        }
        $this->resetErrorBag('items');
        $this->step = 3;
    }

    public function submit(): void
    {
        $this->validate([
            'type'    => ['required', Rule::in(['exchange', 'return'])],
            'reason'  => ['required', Rule::in(array_keys(ReturnRequest::reasons()))],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'type.required'   => 'Selecione o tipo (troca ou devolução).',
            'reason.required' => 'Selecione o motivo.',
        ]);

        if (empty($this->selectedItemIds)) {
            $this->addError('items', 'Selecione pelo menos 1 produto.');
            $this->step = 2;
            return;
        }

        $returnRequest = ReturnRequest::create([
            'customer_id' => auth('customer')->id(),
            'order_id'    => $this->selectedOrderId,
            'type'        => $this->type,
            'status'      => 'pending',
            'reason'      => $this->reason,
            'message'     => $this->message ?: null,
        ]);

        foreach ($this->selectedItemIds as $itemId) {
            $qty = max(1, (int) ($this->itemQuantities[$itemId] ?? 1));
            $returnRequest->items()->create([
                'order_item_id' => (int) $itemId,
                'quantity'      => $qty,
            ]);
        }

        // Notificação no painel admin
        try {
            $order       = $returnRequest->order;
            $customer    = $returnRequest->customer;
            $typeLabel   = $returnRequest->type_label;
            $reasonLabel = $returnRequest->reason_label;

            FilamentNotification::make()
                ->title("Nova solicitação de {$typeLabel}")
                ->body("#{$returnRequest->id} — {$customer?->display_name} | Pedido #{$order?->order_number} | {$reasonLabel}")
                ->icon('heroicon-o-arrow-path')
                ->iconColor('warning')
                ->actions([
                    NotificationAction::make('ver')
                        ->label('Ver solicitação')
                        ->url(\App\Filament\Resources\ReturnRequestResource::getUrl('edit', ['record' => $returnRequest->id]))
                        ->button(),
                ])
                ->sendToDatabase(User::all());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ReturnRequestManager: falha ao enviar notificação admin', [
                'return_request_id' => $returnRequest->id,
                'error'             => $e->getMessage(),
            ]);
        }

        $this->mode = 'list';
        session()->flash('return_success', 'Solicitação enviada com sucesso! Entraremos em contato em breve.');
    }

    public function render()
    {
        $customerId = auth('customer')->id();

        $deliveredOrders = Order::where('customer_id', $customerId)
            ->whereIn('status', ['delivered'])
            ->with('items')
            ->latest()
            ->get();

        $requests = ReturnRequest::where('customer_id', $customerId)
            ->with(['order', 'items.orderItem'])
            ->latest()
            ->get();

        $selectedOrder = $this->selectedOrderId
            ? Order::with('items')->find($this->selectedOrderId)
            : null;

        return view('livewire.account.return-request-manager', compact(
            'deliveredOrders',
            'requests',
            'selectedOrder',
        ));
    }
}
