<div id="avaliacoes" class="scroll-mt-24">

    {{-- ── Cabeçalho ──────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl font-semibold text-gray-900">Avaliações</h2>

        @auth('customer')
            @if ($this->canReview && ! $showForm)
                <button wire:click="openForm"
                    class="px-5 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors">
                    Escrever avaliação
                </button>
            @endif
        @endauth
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

    {{-- ── Resumo de notas ────────────────────────────────────────────── --}}
    @if ($this->product->rating_count > 0)
    <div class="bg-gray-50 rounded-2xl p-6 mb-8 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
        {{-- Nota média --}}
        <div class="text-center sm:text-left shrink-0">
            <p class="text-5xl font-bold text-gray-900">{{ number_format($this->product->rating_avg, 1, ',', '') }}</p>
            <div class="flex items-center gap-0.5 mt-1 justify-center sm:justify-start">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= round($this->product->rating_avg) ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $this->product->rating_count }} {{ $this->product->rating_count === 1 ? 'avaliação' : 'avaliações' }}</p>
        </div>

        {{-- Distribuição por estrela --}}
        <div class="flex-1 w-full space-y-1.5">
            @foreach ($this->ratingDistribution as $star => $count)
                @php $pct = $this->product->rating_count > 0 ? ($count / $this->product->rating_count) * 100 : 0; @endphp
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-4 text-right text-gray-600 font-medium">{{ $star }}</span>
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <div class="flex-1 h-2.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-400 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="w-8 text-right text-gray-400 text-xs">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Formulário de avaliação ────────────────────────────────────── --}}
    @if ($showForm)
    <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8" x-data="{ hover: 0 }">
        <h3 class="text-lg font-semibold text-gray-900 mb-5">Sua avaliação</h3>

        <form wire:submit="submitReview" class="space-y-5">
            {{-- Estrelas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nota</label>
                <div class="flex gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                    <button type="button"
                        wire:click="$set('formRating', {{ $i }})"
                        @mouseenter="hover = {{ $i }}"
                        @mouseleave="hover = 0"
                        class="text-3xl transition-colors cursor-pointer"
                        :class="(hover >= {{ $i }} || @js($formRating) >= {{ $i }}) ? 'text-amber-400' : 'text-gray-300'">
                        &#9733;
                    </button>
                    @endfor
                </div>
                @error('formRating')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Título --}}
            <div>
                <label for="review-title" class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input wire:model="formTitle" type="text" id="review-title" maxlength="120" placeholder="Resuma sua experiência"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none">
            </div>

            {{-- Comentário --}}
            <div>
                <label for="review-comment" class="block text-sm font-medium text-gray-700 mb-1">Comentário</label>
                <textarea wire:model="formComment" id="review-comment" rows="4" maxlength="2000"
                    placeholder="Conte sua experiência com o produto..."
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none resize-none"></textarea>
                @error('formComment')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fotos --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fotos <span class="text-gray-400 font-normal">(até 5, máx 5MB cada)</span></label>

                @if (count($formPhotos) > 0)
                <div class="flex gap-3 flex-wrap mb-3">
                    @foreach ($formPhotos as $index => $photo)
                        <div class="relative group">
                            <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                            <button type="button" wire:click="removePhoto({{ $index }})"
                                class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>
                @endif

                @if (count($formPhotos) < 5)
                <label class="inline-flex items-center gap-2 px-4 py-2 border border-dashed border-gray-300 rounded-xl text-sm text-gray-600 hover:border-gray-400 hover:text-gray-800 cursor-pointer transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                    </svg>
                    Adicionar foto
                    <input type="file" wire:model="formPhotos" accept="image/*" multiple class="hidden">
                </label>
                @endif

                @error('formPhotos')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                @error('formPhotos.*')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ações --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors disabled:opacity-60">
                    <span wire:loading.remove wire:target="submitReview">Enviar avaliação</span>
                    <span wire:loading wire:target="submitReview">Enviando...</span>
                </button>
                <button type="button" wire:click="closeForm"
                    class="px-5 py-2.5 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── Mensagens de estado ────────────────────────────────────────── --}}
    @guest('customer')
        @if ($this->product->rating_count === 0)
            <p class="text-sm text-gray-500 mb-6">
                Este produto ainda não possui avaliações.
                <a href="{{ route('account.login') }}" class="text-gray-900 font-medium hover:underline">Entre na sua conta</a>
                para ser o primeiro a avaliar.
            </p>
        @endif
    @endguest

    @auth('customer')
        @if ($this->existingReview && ! $showForm)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                Você já avaliou este produto.
                @if ($this->existingReview->status === 'pending')
                    Sua avaliação está aguardando moderação.
                @endif
            </div>
        @elseif (! $this->canReview && ! $this->existingReview && ! $showForm)
            @if ($this->product->rating_count === 0)
                <p class="text-sm text-gray-500 mb-6">
                    Este produto ainda não possui avaliações. Compre e receba o produto para ser o primeiro a avaliar.
                </p>
            @endif
        @endif
    @endauth

    {{-- ── Lista de avaliações ────────────────────────────────────────── --}}
    @if ($reviews->isNotEmpty())
    <div class="space-y-6">
        @foreach ($reviews as $review)
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    {{-- Estrelas --}}
                    <div class="flex items-center gap-0.5 mb-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>

                    @if ($review->title)
                        <h4 class="font-semibold text-gray-900 text-sm">{{ $review->title }}</h4>
                    @endif
                </div>

                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                    <p class="text-xs text-gray-400">por {{ $review->customer->display_name ?? 'Cliente' }}</p>
                </div>
            </div>

            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $review->comment }}</p>

            {{-- Fotos --}}
            @if ($review->getMedia('photos')->isNotEmpty())
            @php
                $reviewPhotos = $review->getMedia('photos')->map(fn ($m) => $m->getUrl('optimized') ?: $m->getUrl())->values()->toArray();
            @endphp
            <div class="flex gap-2 flex-wrap mt-4">
                @foreach ($review->getMedia('photos') as $idx => $media)
                    <button type="button"
                        @click="$dispatch('open-lightbox', { images: @js($reviewPhotos), index: {{ $idx }} })"
                        class="focus:outline-none">
                        <img src="{{ $media->getUrl('thumb') }}"
                            alt="Foto da avaliação"
                            class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg border border-gray-200 hover:border-gray-400 transition-colors cursor-pointer">
                    </button>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $reviews->links() }}
    </div>
    @elseif ($this->product->rating_count === 0 && ! session()->has('review_success'))
        @guest('customer')
        @else
            @if (! $this->existingReview && ! $this->canReview && ! $showForm)
            {{-- No reviews yet, no eligibility - already handled above --}}
            @endif
        @endguest
    @endif

</div>
