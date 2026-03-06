<div>

{{-- Flash de sucesso --}}
@if (session('return_success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    {{ session('return_success') }}
</div>
@endif

@if ($mode === 'list')

    {{-- ── MODO LISTA ─────────────────────────────────────────────────── --}}

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="font-medium text-gray-900">Trocas e Devoluções</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gerencie suas solicitações de troca e devolução</p>
        </div>
        <button wire:click="startNew"
                class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova solicitação
        </button>
    </div>

    @if ($requests->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-900 mb-1">Nenhuma solicitação ainda</p>
        <p class="text-xs text-gray-400 mb-5">Você pode solicitar trocas ou devoluções de pedidos entregues.</p>
        <button wire:click="startNew"
                class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
            Fazer uma solicitação
        </button>
    </div>

    @else
    <div class="space-y-3">
        @foreach ($requests as $request)
        @php
            $sc = match($request->status) {
                'pending'     => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'border' => 'border-amber-200',  'dot' => 'bg-amber-400'],
                'approved'    => ['bg' => 'bg-green-50',  'text' => 'text-green-700',  'border' => 'border-green-200',  'dot' => 'bg-green-500'],
                'rejected'    => ['bg' => 'bg-red-50',    'text' => 'text-red-700',    'border' => 'border-red-200',    'dot' => 'bg-red-400'],
                'in_progress' => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'border' => 'border-blue-200',   'dot' => 'bg-blue-400'],
                'completed'   => ['bg' => 'bg-gray-100',  'text' => 'text-gray-600',   'border' => 'border-gray-200',   'dot' => 'bg-gray-400'],
                default       => ['bg' => 'bg-gray-50',   'text' => 'text-gray-600',   'border' => 'border-gray-200',   'dot' => 'bg-gray-400'],
            };
            $tc = $request->type === 'exchange'
                ? ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200']
                : ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200'];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 flex flex-wrap items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <p class="text-sm font-semibold text-gray-900">Solicitação #{{ $request->id }}</p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border {{ $tc['bg'] }} {{ $tc['text'] }} {{ $tc['border'] }}">
                            {{ $request->type_label }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                            {{ $request->status_label }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">
                        Pedido <span class="font-medium text-gray-700">#{{ $request->order?->order_number }}</span>
                        · {{ $request->created_at->format('d/m/Y') }}
                        · {{ $request->reason_label }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-400">
                        {{ $request->items->count() }} produto{{ $request->items->count() != 1 ? 's' : '' }}
                    </p>
                </div>
            </div>

            {{-- Itens da solicitação --}}
            <div class="border-t border-gray-100 divide-y divide-gray-50">
                @foreach ($request->items as $rItem)
                @php $oi = $rItem->orderItem; @endphp
                <div class="px-5 py-2.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm text-gray-800 truncate">{{ $oi?->product_name ?? '—' }}</p>
                        @if ($oi?->variant_label)
                            <p class="text-xs text-gray-400">{{ $oi->variant_label }}</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 shrink-0">Qtd: {{ $rItem->quantity }}</p>
                </div>
                @endforeach
            </div>

            @if ($request->message || $request->admin_notes)
            <div class="border-t border-gray-100 px-5 py-3 bg-gray-50 space-y-2">
                @if ($request->message)
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Sua mensagem</p>
                    <p class="text-xs text-gray-600">{{ $request->message }}</p>
                </div>
                @endif
                @if ($request->admin_notes)
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Resposta da loja</p>
                    <p class="text-xs text-gray-700">{{ $request->admin_notes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

@else

    {{-- ── MODO CRIAR — multi-step ─────────────────────────────────────── --}}

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Nova Solicitação</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Etapa {{ $step }} de 3 —
                @if ($step === 1) Selecione o pedido
                @elseif ($step === 2) Selecione os produtos
                @else Motivo e detalhes
                @endif
            </p>
        </div>
        <button wire:click="cancelCreate"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 border border-gray-200 hover:border-gray-300 px-3 py-1.5 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
            Cancelar
        </button>
    </div>

    {{-- Stepper --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach ([1 => 'Pedido', 2 => 'Produtos', 3 => 'Detalhes'] as $n => $label)
        <div class="flex items-center gap-2 {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2 shrink-0">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $step >= $n ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-400' }}">
                    @if ($step > $n)
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @else
                        {{ $n }}
                    @endif
                </div>
                <span class="text-xs font-medium hidden sm:inline {{ $step >= $n ? 'text-gray-900' : 'text-gray-400' }}">{{ $label }}</span>
            </div>
            @if (!$loop->last)
            <div class="flex-1 h-px {{ $step > $n ? 'bg-gray-900' : 'bg-gray-200' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ── STEP 1: Selecionar Pedido ─── --}}
    @if ($step === 1)
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Selecione o pedido</h2>
            <p class="text-xs text-gray-500 mt-0.5">Apenas pedidos com status "Entregue" estão disponíveis para solicitação.</p>
        </div>

        @if ($deliveredOrders->isEmpty())
        <div class="p-8 text-center">
            <p class="text-sm text-gray-500">Nenhum pedido entregue encontrado.</p>
            <p class="text-xs text-gray-400 mt-1">Só é possível solicitar trocas/devoluções de pedidos com status "Entregue".</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach ($deliveredOrders as $order)
            <button wire:click="selectOrder({{ $order->id }})"
                    class="w-full flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50 transition-colors text-left group">
                <div>
                    <p class="text-sm font-medium text-gray-900 group-hover:text-gray-900">Pedido #{{ $order->order_number }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $order->created_at->format('d/m/Y') }}
                        · {{ $order->items->count() }} produto{{ $order->items->count() != 1 ? 's' : '' }}
                        · R$ {{ number_format($order->total, 2, ',', '.') }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-600 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </button>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- ── STEP 2: Selecionar Produtos ─── --}}
    @if ($step === 2 && $selectedOrder)
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Selecione os produtos</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pedido #{{ $selectedOrder->order_number }} — marque os itens que deseja trocar/devolver</p>
                </div>
                <button wire:click="backToStep(1)"
                        class="text-xs text-gray-500 hover:text-gray-900 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Voltar
                </button>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach ($selectedOrder->items as $item)
                @php $checked = in_array((string)$item->id, $selectedItemIds) || in_array($item->id, $selectedItemIds); @endphp
                <div class="px-5 py-4 flex items-center gap-4 {{ $checked ? 'bg-blue-50/50' : '' }} transition-colors">
                    <input type="checkbox"
                           wire:model="selectedItemIds"
                           value="{{ $item->id }}"
                           id="item_{{ $item->id }}"
                           class="w-4 h-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900 cursor-pointer shrink-0">

                    <label for="item_{{ $item->id }}" class="flex-1 min-w-0 cursor-pointer">
                        <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                        @if ($item->variant_label)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $item->variant_label }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">
                            Qtd. no pedido: {{ $item->quantity }}
                            · R$ {{ number_format($item->unit_price, 2, ',', '.') }} cada
                        </p>
                    </label>

                    @if ($checked)
                    <div class="shrink-0 flex items-center gap-2">
                        <label class="text-xs text-gray-500">Qtd.:</label>
                        <input type="number"
                               wire:model="itemQuantities.{{ $item->id }}"
                               min="1" max="{{ $item->quantity }}"
                               value="1"
                               class="w-16 rounded-lg border border-gray-300 px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-900">
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        @error('items')
        <p class="text-sm text-red-600 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            {{ $message }}
        </p>
        @enderror

        <div class="flex justify-end">
            <button wire:click="goToStep3"
                    class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
                Continuar
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
    @endif

    {{-- ── STEP 3: Tipo, Motivo e Mensagem ─── --}}
    @if ($step === 3)
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Detalhes da solicitação</h2>
                <button wire:click="backToStep(2)"
                        class="text-xs text-gray-500 hover:text-gray-900 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Voltar
                </button>
            </div>

            <div class="p-5 space-y-5">

                {{-- Tipo --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">Tipo de solicitação</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all
                                      {{ $type === 'return' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="type" value="return" class="sr-only">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                            {{ $type === 'return' ? 'bg-gray-900' : 'bg-gray-100' }}">
                                    <svg class="w-4 h-4 {{ $type === 'return' ? 'text-white' : 'text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Devolução</p>
                                    <p class="text-xs text-gray-500">Reembolso do valor</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all
                                      {{ $type === 'exchange' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="type" value="exchange" class="sr-only">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                            {{ $type === 'exchange' ? 'bg-gray-900' : 'bg-gray-100' }}">
                                    <svg class="w-4 h-4 {{ $type === 'exchange' ? 'text-white' : 'text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Troca</p>
                                    <p class="text-xs text-gray-500">Substituição do produto</p>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('type') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Motivo --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Motivo <span class="text-red-500">*</span></label>
                    <select wire:model="reason"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('reason') border-red-400 @enderror bg-white">
                        <option value="">Selecione o motivo...</option>
                        @foreach (\App\Models\Sales\ReturnRequest::reasons() as $value => $label)
                            <option value="{{ $value }}" @selected($reason === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Mensagem --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Mensagem adicional <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <textarea wire:model="message"
                              rows="4"
                              placeholder="Descreva com mais detalhes o problema ou o que você precisa..."
                              class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent resize-none @error('message') border-red-400 @enderror"></textarea>
                    @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">Máximo 1.000 caracteres.</p>
                </div>

                {{-- Resumo --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Resumo da solicitação</p>
                    <div class="space-y-1 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Pedido</span>
                            <span class="font-medium text-gray-900">#{{ $selectedOrder?->order_number }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Produtos selecionados</span>
                            <span class="font-medium text-gray-900">{{ count($selectedItemIds) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button wire:click="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-6 py-2.5 rounded-xl hover:bg-gray-700 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="submit">Enviar solicitação</span>
                <span wire:loading wire:target="submit">Enviando...</span>
                <svg wire:loading.remove wire:target="submit" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
            </button>
        </div>
    </div>
    @endif

@endif

</div>
