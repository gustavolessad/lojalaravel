@php
    $banners = \App\Models\Banner::where('group', 'principal')
        ->where('active', true)
        ->orderBy('order')
        ->get();
@endphp

@if ($banners->isNotEmpty())
<div
    x-data="{
        current: 0,
        total: {{ $banners->count() }},
        timer: null,
        touchStartX: 0,
        start() {
            this.timer = setInterval(() => this.next(), 5000);
        },
        stop() {
            clearInterval(this.timer);
        },
        next() {
            this.current = (this.current + 1) % this.total;
        },
        prev() {
            this.current = (this.current - 1 + this.total) % this.total;
        },
        goto(idx) {
            this.current = idx;
        },
        onTouchStart(e) {
            this.touchStartX = e.touches[0].clientX;
        },
        onTouchEnd(e) {
            const diff = this.touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) diff > 0 ? this.next() : this.prev();
        }
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
    @touchstart="onTouchStart($event)"
    @touchend="onTouchEnd($event)"
    class="relative w-full overflow-hidden bg-gray-100"
>
    {{-- Espaçador: mantém a altura do container sem ocupar espaço visual --}}
    {{-- Usa a imagem do primeiro banner para determinar a proporção --}}
    <div aria-hidden="true" class="invisible w-full">
        <picture class="block w-full">
            <source media="(max-width: 767px)" srcset="{{ $banners->first()->mobileUrl() }}">
            <img src="{{ $banners->first()->desktopUrl() }}" alt="" class="w-full h-auto">
        </picture>
    </div>

    {{-- Slides: empilhados via absolute, transição de opacidade --}}
    @foreach ($banners as $index => $banner)
        <div
            class="absolute inset-0 transition-opacity duration-700"
            :class="current === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0'"
        >
            @if ($banner->link)
                <a
                    href="{{ $banner->link }}"
                    @if ($banner->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                    class="block w-full h-full"
                >
                    <picture class="block w-full h-full">
                        <source media="(max-width: 767px)" srcset="{{ $banner->mobileUrl() }}">
                        <img
                            src="{{ $banner->desktopUrl() }}"
                            alt="{{ $banner->title }}"
                            class="w-full h-full object-cover"
                            @if ($index === 0) loading="eager" @else loading="lazy" @endif
                        >
                    </picture>
                </a>
            @else
                <picture class="block w-full h-full">
                    <source media="(max-width: 767px)" srcset="{{ $banner->mobileUrl() }}">
                    <img
                        src="{{ $banner->desktopUrl() }}"
                        alt="{{ $banner->title }}"
                        class="w-full h-full object-cover"
                        @if ($index === 0) loading="eager" @else loading="lazy" @endif
                    >
                </picture>
            @endif
        </div>
    @endforeach

    @if ($banners->count() > 1)
        {{-- Seta anterior --}}
        <button
            @click="prev()"
            class="absolute left-3 top-1/2 -translate-y-1/2 z-20 bg-black/30 hover:bg-black/50 text-white rounded-full w-9 h-9 flex items-center justify-center transition"
            aria-label="Anterior"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        {{-- Seta próxima --}}
        <button
            @click="next()"
            class="absolute right-3 top-1/2 -translate-y-1/2 z-20 bg-black/30 hover:bg-black/50 text-white rounded-full w-9 h-9 flex items-center justify-center transition"
            aria-label="Próximo"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        {{-- Dots --}}
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
            @foreach ($banners as $index => $banner)
                <button
                    @click="goto({{ $index }})"
                    :class="current === {{ $index }} ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/75'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="Ir para slide {{ $index + 1 }}"
                ></button>
            @endforeach
        </div>
    @endif
</div>
@endif
