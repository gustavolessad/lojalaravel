{{-- Lightbox global — dispare com: $dispatch('open-lightbox', { images: [...urls], index: 0 }) --}}
<div
    x-data="{
        open: false,
        images: [],
        idx: 0,
        show(data) {
            this.images = data.images || [];
            this.idx    = data.index  || 0;
            if (this.images.length) this.open = true;
        },
        close() { this.open = false },
        prev()  { this.idx = (this.idx - 1 + this.images.length) % this.images.length },
        next()  { this.idx = (this.idx + 1) % this.images.length },
    }"
    @open-lightbox.window="show($event.detail)"
    @keydown.window="if (open) {
        if ($event.key === 'ArrowLeft')  { $event.preventDefault(); prev() }
        if ($event.key === 'ArrowRight') { $event.preventDefault(); next() }
        if ($event.key === 'Escape')     { close() }
    }"
    x-cloak>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="close()"
        class="fixed inset-0 z-50 bg-black/90 flex flex-col items-center justify-center p-4 sm:p-8"
        style="display: none;">

        {{-- Fechar --}}
        <button @click="close()"
            class="absolute top-4 right-4 bg-white/10 hover:bg-white/25 text-white/80 hover:text-white rounded-full p-2 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Contador --}}
        <div x-show="images.length > 1"
            class="absolute top-4 left-4 text-white/50 text-sm tabular-nums select-none">
            <span x-text="idx + 1"></span> / <span x-text="images.length"></span>
        </div>

        {{-- Imagem + navegação --}}
        <div class="relative flex items-center justify-center w-full max-w-5xl">
            <button @click.stop="prev()" x-show="images.length > 1"
                class="absolute left-0 sm:-left-14 z-10 bg-white/10 hover:bg-white/25 text-white rounded-full p-2 sm:p-3 transition-colors">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <img :src="images[idx]" alt="" class="max-h-[75vh] max-w-full object-contain rounded-xl select-none" @click.stop>

            <button @click.stop="next()" x-show="images.length > 1"
                class="absolute right-0 sm:-right-14 z-10 bg-white/10 hover:bg-white/25 text-white rounded-full p-2 sm:p-3 transition-colors">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>

        {{-- Miniaturas --}}
        <div x-show="images.length > 1" class="flex gap-2 mt-5 overflow-x-auto max-w-full pb-1">
            <template x-for="(img, i) in images" :key="i">
                <button @click.stop="idx = i"
                    :class="idx === i ? 'opacity-100' : 'opacity-40 hover:opacity-75'"
                    class="w-14 h-14 rounded-xl overflow-hidden shrink-0 transition-opacity duration-150">
                    <img :src="img" class="w-full h-full object-cover">
                </button>
            </template>
        </div>
    </div>
</div>
