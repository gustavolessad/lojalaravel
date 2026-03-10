<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">Minhas Avaliações</h1>
    </div>

    {{-- Flash --}}
    @if (session()->has('review_success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('review_success') }}
        </div>
    @endif

    @if ($reviews->isEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
            </svg>
            <p class="text-gray-500 text-sm">Você ainda não fez nenhuma avaliação.</p>
            <p class="text-gray-400 text-xs mt-1">Avaliações podem ser feitas na página de cada produto após receber o pedido.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($reviews as $review)
            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                {{-- Modo edição --}}
                @if ($editingId === $review->id)
                <div x-data="{ hover: 0 }">
                    <h3 class="font-semibold text-gray-900 text-sm mb-4">Editar avaliação</h3>
                    <form wire:submit="updateReview" class="space-y-4">
                        {{-- Estrelas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nota</label>
                            <div class="flex gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    wire:click="$set('editRating', {{ $i }})"
                                    @mouseenter="hover = {{ $i }}"
                                    @mouseleave="hover = 0"
                                    class="text-2xl transition-colors cursor-pointer"
                                    :class="(hover >= {{ $i }} || @js($editRating) >= {{ $i }}) ? 'text-amber-400' : 'text-gray-300'">
                                    &#9733;
                                </button>
                                @endfor
                            </div>
                            @error('editRating') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-gray-400 font-normal">(opcional)</span></label>
                            <input wire:model="editTitle" type="text" maxlength="120"
                                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comentário</label>
                            <textarea wire:model="editComment" rows="3" maxlength="2000"
                                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none resize-none"></textarea>
                            @error('editComment') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors">
                                Salvar
                            </button>
                            <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                @else
                {{-- Modo visualização --}}
                <div class="flex gap-4">
                    {{-- Thumbnail do produto --}}
                    <a href="{{ route('product.show', $review->product->slug) }}" class="shrink-0">
                        <img src="{{ $review->product->getFirstMediaUrl('cover', 'thumb') ?: 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2264%22 height=%2264%22/>' }}"
                            alt="{{ $review->product->name }}"
                            class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                    </a>

                    <div class="flex-1 min-w-0">
                        {{-- Produto + Status --}}
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <a href="{{ route('product.show', $review->product->slug) }}" class="text-sm font-medium text-gray-900 hover:underline truncate">
                                {{ $review->product->name }}
                            </a>
                            <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ match($review->status) {
                                    'pending'  => 'bg-amber-50 text-amber-700',
                                    'approved' => 'bg-green-50 text-green-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                    default    => 'bg-gray-50 text-gray-700',
                                } }}">
                                {{ $review->status_label }}
                            </span>
                        </div>

                        {{-- Estrelas + Data --}}
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-400">{{ $review->created_at->format('d/m/Y') }}</span>
                        </div>

                        @if ($review->title)
                            <p class="text-sm font-semibold text-gray-800 mb-1">{{ $review->title }}</p>
                        @endif
                        <p class="text-sm text-gray-600 line-clamp-3">{{ $review->comment }}</p>

                        {{-- Fotos --}}
                        @if ($review->getMedia('photos')->isNotEmpty())
                        <div class="flex gap-2 mt-3">
                            @foreach ($review->getMedia('photos') as $media)
                                <img src="{{ $media->getUrl('thumb') }}" alt="Foto" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @endforeach
                        </div>
                        @endif

                        {{-- Ações --}}
                        <div class="flex items-center gap-3 mt-3" x-data="{ confirmDelete: false }">
                            @if ($review->status === 'pending')
                            <button wire:click="startEdit({{ $review->id }})"
                                class="text-xs text-gray-500 hover:text-gray-800 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                </svg>
                                Editar
                            </button>
                            @endif

                            <button @click="confirmDelete = true"
                                x-show="!confirmDelete"
                                class="text-xs text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Excluir
                            </button>

                            <div x-show="confirmDelete" x-cloak class="flex items-center gap-2">
                                <span class="text-xs text-red-600">Confirmar exclusão?</span>
                                <button wire:click="deleteReview({{ $review->id }})"
                                    class="text-xs font-medium text-red-600 hover:text-red-800 transition-colors">Sim</button>
                                <button @click="confirmDelete = false"
                                    class="text-xs text-gray-500 hover:text-gray-700 transition-colors">Não</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            @endforeach
        </div>
    @endif
</div>
